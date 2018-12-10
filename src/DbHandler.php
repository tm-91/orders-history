<?php


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
                    \Bootstraper::logger()->error($e->getMessage());
                    \Bootstraper::logger()->error($e->getTraceAsString());
                    throw $e;
                } else {
                    @header($_SERVER['SERVER_PROTOCOL'] . ' 500 Database connection error', true, 500);
                }
            }
        }
        return self::$_dbHandler;
    }

}