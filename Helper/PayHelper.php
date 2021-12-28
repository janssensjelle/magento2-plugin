<?php

namespace Paynl\Payment\Helper;

use Psr\Log\LoggerInterface;

class PayHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    private static $objectManager;
    private static $store;

    public static function getObjectManager()
    {
        if (empty(self::$objectManager)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            self::$objectManager = $objectManager;
        }
        return self::$objectManager;
    }

    public static function getStore($store)
    {
        if (empty($store)) {
            if (empty(self::$store)) {
                $objectManager = self::getObjectManager();
                $store = $objectManager->get(\Magento\Store\Model\Store::class);
                self::$store = $store;
            }
            return self::$store;
        }
        return $store;
    }

    public static function hasCorrectLevel($level, $type)
    {
        if ($level == 2 && $type == 'critical') {
            return true;
        }
        if ($level == 1 && ($type == 'critical' || $type == 'notice')) {
            return true;
        }
        if ($level == 0) {
            return true;
        }

        return false;
    }

    public static function logCritical($text, $params = array(), $store = null)
    {
        self::writeLog($text, 'critical', $params, $store);
    }

    public static function logNotice($text, $params = array(), $store = null)
    {
        self::writeLog($text, 'notice', $params, $store);
    }

    public static function logInfo($text, $params = array(), $store = null)
    {
        self::writeLog($text, 'info', $params, $store);
    }

    public static function logDebug($text, $params = array(), $store = null)
    {
        self::writeLog($text, 'debug', $params, $store);
    }

    public static function writeLog($text, $type, $params, $store)
    {
        $objectManager = self::getObjectManager();
        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);

        $store = self::getStore($store);
        $level = $store->getConfig('payment/paynl/logging_level');

        if (self::hasCorrectLevel($level, $type)) {
            $prefix = 'PAY.: ';
            $text = $prefix . $text;
            if (!is_array($params)) {
                $params = array();
            }
            switch ($type) {
                case 'critical':
                    $logger->critical($text, $params);
                    break;
                case 'notice':
                    $logger->notice($text, $params);
                    break;
                case 'info':
                    $logger->info($text, $params);
                    break;
                case 'debug':
                    $logger->debug($text, $params);
                    break;
            }
        }
    }

    public function getClientIp()
    {
        $ipforward = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        return !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $ipforward;
    }
}
