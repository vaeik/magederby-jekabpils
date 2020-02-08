<?php
namespace MageDerby\ProductLimit\Setup\Patch\Data;

use Exception;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddVipGroup
 * @package Magento\DummyModule\Setup\Patch\Data
 */
class AddVipGroup implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var GroupInterfaceFactory
     */
    private $groupFactory;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * AddVipGroup constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param GroupInterfaceFactory $groupFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param Config $resourceConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        GroupInterfaceFactory $groupFactory,
        GroupRepositoryInterface $groupRepository,
        Config $resourceConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $group = $this->groupFactory->create();
            $group->setCode('Vip')
                ->setTaxClassId(3);

            $this->resourceConfig->saveConfig(
                'magederby_product_limit_settings/general/bypass_group',
                $this->groupRepository->save($group)->getId()
            );
        } catch (Exception $e) {
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
