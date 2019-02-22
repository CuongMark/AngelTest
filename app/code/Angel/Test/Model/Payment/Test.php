<?php


namespace Angel\Test\Model\Payment;

class Test extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "test";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
