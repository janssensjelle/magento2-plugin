<?php

namespace Paynl\Payment\Block\Adminhtml\Render;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Paynl\Payment\Helper\PayHelper;
use \Paynl\Paymentmethods;

class Credentials extends Field
{

    protected $_template = 'Paynl_Payment::system/config/credentials.phtml';

    /**
     *
     * @var Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     *
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(Context $context, RequestInterface $request, ScopeConfigInterface $scopeConfig)
    {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setNamePrefix($element->getName())->setHtmlId($element->getHtmlId());
        return $this->_toHtml();
    }

    /**
     * Check the Credentials
     * @return array
     */
    public function checkCredentials()
    {
        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website');

        $scope = 'default';
        $scopeId = 0;

        if ($storeId) {
            $scope = 'stores';
            $scopeId = $storeId;
        }
        if ($websiteId) {
            $scope = 'websites';
            $scopeId = $websiteId;
        }

        $tokencode = $this->scopeConfig->getValue('payment/paynl/tokencode', $scope, $scopeId);
        $apiToken = $this->scopeConfig->getValue('payment/paynl/apitoken_encrypted', $scope, $scopeId);
        $serviceId = $this->scopeConfig->getValue('payment/paynl/serviceid', $scope, $scopeId);
        $gateway = $this->scopeConfig->getValue('payment/paynl/failover_gateway', $scope, $scopeId);

        $error = '';
        $status = 1;
        if (!empty($apiToken) && !empty($serviceId) && !empty($tokencode)) {
            try {
                if (!empty($gateway) && substr(trim($gateway), 0, 4) === "http") {
                    \Paynl\Config::setApiBase(trim($gateway));
                }
                \Paynl\Config::setTokenCode($tokencode);
                \Paynl\Config::setApiToken($apiToken);
                \Paynl\Config::setServiceId($serviceId);

                Paymentmethods::getList();
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        } else if (!empty($apiToken) || !empty($serviceId) || !empty($tokencode)) {
            $error = __('Pay. Tokencode, API token and serviceId are required.');
        } else {
            $status = 0;
        }

        if (!empty($error)) {
            switch ($error) {
                case 'HTTP/1.0 401 Unauthorized':
                    $error = __('Service-ID, API-Token or Tokencode invalid');
                    break;
                case 'PAY-404 - Service not found':
                    $error = __('Service-ID is invalid.');
                    break;
                case 'PAY-403 - Access denied: Token not valid for this company':
                    $error = __('Service-ID / API-Token combination is invalid.');
                    break;
                default :
                    payHelper::logCritical('Pay. API exception: ' . $error);
                    $error = __('Could not authorize');
            }
            $status = 0;
        }

        return ['status' => $status, 'error' => $error];
    }
}
