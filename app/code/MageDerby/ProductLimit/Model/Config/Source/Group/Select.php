<?php
namespace MageDerby\ProductLimit\Model\Config\Source\Group;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Select
 * @package MageDerby\ProductLimit\Model\Config\Source\Group
 */
class Select implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    protected $customerGroup;

    /**
     * @var $options
     */
    protected $options;

    /**
     * Select constructor.
     * @param Collection $customerGroup
     */
    public function __construct(Collection $customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->customerGroup->toOptionArray();
        }

        return $this->options;
    }
}
