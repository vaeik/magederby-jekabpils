<?php

namespace MageDerby\ProductLimit\Plugin;

use DateTime;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;

/**
 * Class BeforeProductAdd
 *
 * @package MageDerby\ProductLimit\Plugin
 */
class BeforeProductAddPlugin
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var $scent
     */
    protected $scent;

    /**
     * BeforeProductAddPlugin constructor.
     *
     * @param Session                    $customerSession
     * @param ResourceConnection         $resourceConnection
     * @param ScopeConfigInterface       $scopeConfig
     * @param Url                        $customerUrl
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Session $customerSession,
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig,
        Url $customerUrl,
        ProductRepositoryInterface $productRepository
    ) {
        $this->customerSession = $customerSession;
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
        $this->customerUrl = $customerUrl;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Quote $subject
     * @param       $product
     *
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        Quote $subject,
        $product,
        $requestInfo
    ) {
        $enabled = $this->scopeConfig->getValue(
            'magederby_product_limit_settings/general/enabled',
            ScopeInterface::SCOPE_STORE
        );

        if ($enabled) {
            $customerId = $subject->getCustomerId();

            if (!$customerId) {
                throw new LocalizedException(
                    __('You must be logged in to purchase this product.')
                );
            }

            // Define scent.
            $this->scent = $product->getScent();
            $amount = 0;

            // Define current amount
            if ($requestInfo) {
                $amount = $requestInfo->getQty();
            }

            try {
                $amount += $this->matchOrderItems($customerId);
            } catch (Exception $e) {
            }

            $amount += $this->matchCartItems($subject);
            $customerGroup = $subject->getCustomer()->getGroupId();
            $amountLimit = $this->scopeConfig->getValue(
                'magederby_product_limit_settings/general/amount_limit',
                ScopeInterface::SCOPE_STORE
            );
            $bypassGroup = $this->scopeConfig->getValue(
                'magederby_product_limit_settings/general/bypass_group',
                ScopeInterface::SCOPE_STORE
            );

            if ((int)$amount > (int)$amountLimit && $customerGroup !== $bypassGroup) {
                throw new LocalizedException(
                    __('You have reached the maximum purchase limit of the product.')
                );
            }
        }
    }

    /**
     * @param $customerId
     *
     * @return int
     * @throws Exception
     */
    public function matchOrderItems($customerId)
    {
        $match = 0;
        $timeLimit = $this->scopeConfig->getValue(
            'magederby_product_limit_settings/general/time_limit',
            ScopeInterface::SCOPE_STORE
        );

        $endDate = (new DateTime())->format('Y-m-d H:i:s');
        $startDate = (new DateTime())->modify('-' . $timeLimit . ' day')->format('Y-m-d H:i:s');

        $products = $this->resourceConnection->getConnection()->select()
            ->from(['o' => 'sales_order'])
            ->joinLeft(['sop' => 'sales_order_item'], 'sop.order_id = o.entity_id')
            ->columns('sop.sku')
            ->where('o.customer_id = ' . $customerId)
            ->where('o.created_at >= "' . $startDate . '"')
            ->where('o.created_at <= "' . $endDate . '"')
            ->where('sop.scent like "%' . $this->scent . '%"')
            ->query()->fetchAll();

        foreach ($products as $key => $value) {
            $match += $value['qty_ordered'];
        }

        return $match;
    }

    /**
     * @param $cartItems
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function matchCartItems($cartItems)
    {
        $match = 0;

        foreach ($cartItems->getAllItems() as $item) {
            $attribute = $item->getProduct()->getScent();

            if ($attribute && $attribute === $this->scent) {
                $match += $item->getQty();
            }
        }

        return $match;
    }
}
