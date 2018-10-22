<?php
namespace Test\Controller;
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-10-10
 * Time: 13:19
 */
class Index extends \Application\Controller\ControllerAbstract
{

    function __construct(){
        echo "\njestem w test!\n";
    }

    function indexAction()
    {

        $recDiff = function ($array1, $array2) use (&$recDiff) {
            $outcome = ['e'=>[], 'r'=>[]];
//            $history = new \Application\Model\Entity\OrderChange($this->getShopId(), $this->getId());
//            $history = new \Application\Model\Entity\OrderChange(1, 1);
            foreach ($array1 as $key1 => $val1) {
                if (array_key_exists($key1, $array2)) {
                    if (is_array($val1)) {
                        $out = $recDiff($val1, $array2[$key1]);
                        // array_merge($outcome['e'], $out['e']);
                        if ($out['e']){
//                        if ($edited = $out->getEditedData()) {
                            $outcome['e'][$key1] = $out['e'];
//                            $history->addEditedData([$key1 => $edited]);
                        }
                        if ($out['r']){
//                        if ($removed = $out->getRemovedData()) {
                            $outcome['r'][$key1] = $out['r'];
//                            $history->addRemovedData([$key1 => $removed]);
                        }
                    } else {
                        if ($val1 !== $array2[$key1]) {
                            $outcome['e'][$key1] = $array2[$key1];
//                            $history->addEditedData([$key1 => $array2[$key1]]);
                        }
                    }
                } else {
                    $outcome['r'][$key1] = $val1;
//                    $history->addRemovedData([$key1 => $val1]);
                }
            }
            return $outcome;
//            return $history;
        };

        $findAdded = function ($array1, $array2) use (&$findAdded) {
            $outcome = [];
//            $history = new \Application\Model\Entity\OrderChange($this->getShopId(), $this->getId());
//            $history = new \Application\Model\Entity\OrderChange(1,1);
            foreach ($array2 as $key2 => $val2) {
                if (!key_exists($key2, $array1)) {
                        $outcome[$key2] = $val2;
                } elseif (is_array($val2)) {
                    echo "\nis array\n";
                    print_r($val2);
                    if ($out = $findAdded($array1[$key2], $val2)) {
                        echo "\nout:\n";
                        print_r($out);
                        $outcome[$key2] = $out;
                    }

                }
            }
            return $outcome;
//            return $history;
        };

        $data1 = [
            'a' => 'aa',
            'b' => 'bb',
            'c' => [
                'c1' => 'c11',
                'c2' => 'c22',
                'c3' => [
                    'c3a' => 1,
                    'c3b' => 2,
                    'c3c' => 3
                ],
                'c4' => 'c44'
            ],
            'd' => 'dd'
        ];

        $data2 = [
            'a' => 'aa',
            'b' => 'bb',
            'c' => [
                'c1' => 'c11',
                'c2' => 'c22',
                'c3' => [
                    'c3a' => 1,
                    'c3b' => 2,
                    'c3c' => 3
                ],
                'c4' => 'c44'
            ],
            'd' => 'dd'
        ];

        $outcome = $recDiff($data1, $data2);
        $outcome2 = $findAdded($data1, $data2);
        // var_dump($outcome);
        // echo "</br>data1</br>";
        // print_r($data1);
        // echo "</br>data2</br>";
        // print_r($data2);

        echo "</br>zmiany</br>\n";
        echo "</br>edytowane</br>";
        echo print_r($outcome['e'], true);
        echo "</br>usuniete</br>";
        echo print_r($outcome['r'], true);
        echo "</br>dodane</br>";
        echo print_r($outcome2, true);
    }

}