<?php


namespace Angel\Test\Block\Adminhtml\Raffle\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class TestButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Test Generate Random Number'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
