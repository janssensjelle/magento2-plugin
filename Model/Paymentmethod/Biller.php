<?php

namespace Paynl\Payment\Model\Paymentmethod;

use Paynl\Payment\Model\Config;

class Biller extends PaymentMethod
{
    protected $_code = 'paynl_payment_biller';

    protected function getDefaultPaymentOptionId()
    {
        return 2931;
    }
}
