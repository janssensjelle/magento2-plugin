<?php

namespace Paynl\Payment\Model\Paymentmethod;

class Paypal extends PaymentMethod
{
    protected $_code = 'paynl_payment_paypal';

    protected function getDefaultPaymentOptionId()
    {
        return 138;
    }
}
