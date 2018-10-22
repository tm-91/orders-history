<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-10-08
 * Time: 18:41
 */

namespace Application\Model;


class Orders
{
    /**
     * array of \Application\Model\Entity\OrderChange objects
     */
    protected $_historyEntries;

    // todo
    /**
     *
     * @return array of \Application\Model\Entity\OrderChange
     */
    public function getOrderHistory($id)
    {
        // pobierz z bazy danych wszystkie wpisy dotyczące danego zamówienia
        // dla każdego wpisu stwórz obietk Encji i dodaj do tabeli wynikowej
        // zwróć tabele z encjami

    }

    public function removeOrderHistory($id)
    {
        // znajdź i usuń wszyskie wpisy w bd
    }

    /**
     * @param $id
     * @return array
     */
    public function getOrderCurrentState($id)
    {
        // znajdź w bazie danych i pobierz aktualny stan zamówienia

        // todo
        /*
        * co zwrócic? encje czy tablice?
        */
    }



    // przenieść do obiektu operującego na encjach
    /**
     * @param $orderData1
     * @param $orderData2
     * @return \Application\Model\Entity\OrderChange
     *
     */
    public function getOrdersDiff($orderData1, $orderData2)
    {
        // ma pobierać jako argumenty encje czy tablice?

        $recDiff = function ($array1, $array2) use (&$recDiff){
            $outcome = [];
            foreach ($array1 as $a1Key => $a1Val) {
                if (array_key_exists($a1Key, $array2)){
                    if (is_array($a1Val)) {
                        $out = $recDiff($a1Val, $array2[$a1Key]);
                        // array_merge($outcome['e'], $out['e']);
                        $outcome['e'][$a1Key] = $out['e'];
                    } else {
                        if ($a1Val !== $array2[$a1Key]){
                            $outcome['e'][$a1Key] = $array2[$a1Key];
                        }
                    }
                } else {
                    $outcome['r'][$a1Key] = $a1Val;
                }
            }
            return $outcome;
        };

        // zwróć encje reprezentującą wpis w historii
    }


    public function getHistoryEntry($id, $date)
    {
        // pobierz z bazy odpowiedni wpis
        // stwórz encje
        // zwróć encje
    }

    public function pushHistoryEntry(\Application\Model\Entity\OrderChange $data)
    {
        $db = \DbHandler::getDb();
        $stm = $db->prepare('INSERT INTO orders_history (shop_id, order_id, date, changes) VALUES (:shop_id, :order_id, :date, :changes)');
        $stm->bindValue(':shop_id', $data->getShopId(), \PDO::PARAM_INT);
        $stm->bindValue(':order_id', $data->getOrderId(), \PDO::PARAM_INT);
        $stm->bindValue(':date', $data->getDate());
        $stm->bindValue(':changes', $data->getDataJSON(), \PDO::PARAM_STR);
        $stm->execute();
        // todo
        if ($stm === false) {

        }
    }

    public function pushHistoryEntryArray(array $data)
    {
    }

    public function alterHistoryEntry()
    {
    }

    public function removeHistoryEntry()
    {
    }

}