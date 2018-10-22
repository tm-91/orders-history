<?php
namespace Application\Model;

/*
* klasa operująca na bazie danych

- dodaj do baze
- usun z bazy
- edytuj
*/
class OrderHistory
{
    protected $dbHandler = false;

    public function __construct(){
        $this->dbHandler = \Core\DatabaseHandler::getDbHandler()
    }

    protected $_dbHandler = false;

    protected function getDbHandler(){
        if ($this->_dbHandler === false) {

        }
    }

	public function addEntry(){}
	public function getEntry(){}
	public function getOrderEntries(){}
	public function alterEntry(){}
	public function removeEntry(){}
	// + metody pomocnicze do laczenia i obslugi bd
}