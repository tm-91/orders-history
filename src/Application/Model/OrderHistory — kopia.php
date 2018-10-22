<?php
namespace Application\Model;

/*
* klasa operująca na bazie danych
*/
class OrderHistory_kopia
{

	protected $_addedData = [];
	protected $_editedData = [];
	protected $_removedData = [];
	protected $_date;
	protected $_orderId;
	protected $_id;


	// todo
	/**
	*
	* @return array of \Application\Model\Entity\OrderChange
	*/
	public function getOrderHistory($id){
		// pobierz z bazy danych wszystkie wpisy dotyczące danego zamówienia
		// dla każdego wpisu stwórz obietk Encji i dodaj do tabeli wynikowej
		// zwróć tabele z encjami
	}
	
	public function removeOrderHistory($id){
		// znajdź i usuń wszyskie wpisy w bd
	}

	public function getOrderCurrentState($id){
		// znajdź w bazie danych i pobierz aktualny stan zamówienia

		// todo
		/*
		* co zwrócic? encje czy tablice?
		*/
	}



	// przenieść do obiektu operującego na encjach
	public function getOrdersDiff($orderData1, $orderData2){
		// ma pobierać jako argumenty encje czy tablice?

		// zwróć encje reprezentującą wpis w historii
	}


	
	public function getHistoryEntry($id, $date){
		// pobierz z bazy odpowiedni wpis
		// stwórz encje 
		// zwróć encje
	}

	public function pushHistoryEntry(\Application\Model\Entity\OrderChange $data){
		$id;
	}

	public function alterHistoryEntry(){}

	public function removeHistoryEntry(){}
}