<?php
/**
 * Created by PhpStorm.
 * User: mateusz
 * Date: 21.09.18
 * Time: 14:52
 */

class Database
{
    /**
     * @var \PDO
     */
    protected static $_dbHandler = false;

    public static function getDbHandler(){
        if (self::$_dbHandler === false) {
            $connection = \Bootstraper::getConfig('db');
            try {
                self::$_dbHandler = new \PDO($connection['connection'], $connection['user'], $connection['pass']);
            } catch (\PDOException $e){
                if (\Bootstraper::getConfig('debug')){
                    \Bootstraper::log($e->getMessage(), \Psr\Log\LogLevel::ERROR);
                    \Bootstraper::log($e->getTraceAsString(), \Psr\Log\LogLevel::ERROR);
                    throw $e;
                } else {
                    @header($_SERVER['SERVER_PROTOCOL'] . ' 500 Database connection error', true, 500);
                    \Bootstraper::log('Database connection error', \Psr\Log\LogLevel::ERROR);
                }
            }
        }

        self::$_dbHandler->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        
        return self::$_dbHandler;
    }

}