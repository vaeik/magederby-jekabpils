<?php
namespace MageDerby\ProductLimit\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;

/**
 * Class AroundConvertPlugin
 * @package MageDerby\ProductLimit\Plugin
 */
class AroundConvertPlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * AroundConvertPlugin constructor.
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param ToOrderItem $subject
     * @param callable $proceed
     * @param AbstractItem $item
     * @param array $additional
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundConvert(
        ToOrderItem $subject,
        callable $proceed,
        AbstractItem $item,
        $additional = []
    ) {
        $orderItem = $proceed($item, $additional);
        $productId = $item->getProduct()->getId();
        $product = $this->productRepository->getById($productId);
        $orderItem->setScent($product->getScent());

        return $orderItem;
    }
}
