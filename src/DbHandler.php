<?php
/**
 * Created by PhpStorm.
 * User: mateusz
 * Date: 21.09.18
 * Time: 14:52
 */


class DbHandler
{
    /**
     * @var \PDO
     */
    protected static $_dbHandler = false;

    public static function getDb(){
        if (self::$_dbHandler === false) {
            $connection = \Bootstraper::getConfig('db');
            try {
                self::$_dbHandler = new \PDO($connection['connection'], $connection['user'], $connection['pass']);
            } catch (\PDOException $e){
                if (\Bootstraper::getConfig('debug')){
                    \Logger::log($e->getMessage(), \Logger::TYPE_ERROR);
                    \Logger::log($e->getTraceAsString(), \Logger::TYPE_ERROR);
                    throw $e;
                } else {
                    @header($_SERVER['SERVER_PROTOCOL'] . ' 500 Database connection error', true, 500);
                    \Logger::log('Database connection error', \Logger::TYPE_ERROR);
                }
            }
        }
        return self::$_dbHandler;
    }

}