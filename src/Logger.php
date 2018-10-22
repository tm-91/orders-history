<?php

/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-09-28
 * Time: 18:26
 */
class Logger
{
    const TYPE_DEBUG = \Psr\Log\LogLevel::DEBUG;
    const TYPE_ALERT = \Psr\Log\LogLevel::ALERT;
    const TYPE_CRITICAL = \Psr\Log\LogLevel::CRITICAL;
    const TYPE_EMERGENCY = \Psr\Log\LogLevel::EMERGENCY;
    const TYPE_ERROR = \Psr\Log\LogLevel::ERROR;
    const TYPE_INFO = \Psr\Log\LogLevel::INFO;
    const TYPE_NOTICE = \Psr\Log\LogLevel::NOTICE;
    const TYPE_WARNING = \Psr\Log\LogLevel::WARNING;

    protected static $_logger = false;

    public static function log($message, $level = self::TYPE_DEBUG) {
        if (self::$_logger === false) {
            self::$_logger = new \DreamCommerce\ShopAppstoreLib\Logger();
        }
        self::$_logger->log($level, $message);
    }

}