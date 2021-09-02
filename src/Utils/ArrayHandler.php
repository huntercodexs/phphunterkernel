<?php

/**
 * @author Jereelton Teixeira (GitHub: jereelton-devel)
 * @example To handler Array Mask use a array structure with max 10 levels, example:
 * arra1 = [
 *      array2 = [
 *          array3 = [
 *              ...
 *              array10 = [
 *              ]
 *          ]
 *      ]
 * ]
 *
 * TODO: Code refactor and oprimize to array digger
 * TODO: arraySanitize
 * TODO: arrayValueHidden
 * WORK: arrayValueMask
 * */

namespace PhpHunter\Kernel\Utils;

use PhpHunter\Kernel\Utils\GenericTools;
use PhpHunter\Kernel\Controllers\DumperController;

class ArrayHandler extends DumperController
{
    /**
     * Allow debug process
     */
    private bool $arrayProcDebug = false;

    /**
     * When is need search any key into arrayData
    */
    private array $arraySearch = [];

    /**
     * The arrayData that contain all data to process
    */
    private array $arrayData = [];

    /**
     * To storage/save keynames of each array found in arrayData
    */
    private array $arrayPush = [];

    /**
     * Create a local copy of arrayData to handler process
    */
    private array $arrayLocal = [];

    /**
     * Constructor Class
    */
    public function __construct()
    {
    }

    /**
     * Destructor Class
     */
    public function __destruct()
    {
    }

    /**
     * @description Array Maker
     * @param string $str #Mandatory
     * @param string $separator #Optional
     * @return array
     */
    public static function arrayBuilder(string $str, string $separator = ","): array
    {
        $a = array();

        if (strstr($str, $separator)) {
            $str = preg_replace('/[\[\]\"\']/i', '', $str);
            $t = explode($separator, $str);
            for ($i = 0; $i < count($t); $i++)
            {
                array_push($a, trim($t[$i]));
            }
        }

        return $a;
    }

    /**
     * @description Check In Array
     * @param string $search #Mandatory
     * @return bool
     */
    public function checkInArray(string $search): bool
    {
        if (in_array($search, $this->arraySearch)) {
            return true;
        }
        return false;
    }

    /**
     * @description Set Array Data
     * @param array $arr #Mandatory/ByReference
     * @return void
     */
    public function setArrayData(array $arr): void
    {
        $this->arrayData = $arr;
    }

    /**
     * @description Set Array Search
     * @param array $arr #Mandatory/ByReference
     * @return void
     */
    public function setArraySearch(array $arr): void
    {
        $this->arraySearch = $arr;
    }

    /**
     * @description Set Array Push
     * @param string $str #Mandatory
     * @return void
     */
    public function setArrayPush(string $str): void
    {
        //Ex: ['keyname1', 'keyname2', 'keyname3',...]
        array_push($this->arrayPush, $str);
    }

    /**
     * @description reset Array Push
     * @return void
     */
    public function resetArrayPush(): void
    {
        $this->arrayPush = [];
    }

    /**
     * @description Set Array Local
     * @return void
     */
    public function setArrayLocal(): void
    {
        $a = $this->arrayPush;

        /*Limit: 10 (levels)*/
        switch (count($a)) {
            case 1:
                $this->arrayLocal[$a[0]] = [];
                break;
            case 2:
                $this->arrayLocal[$a[0]][$a[1]] = [];
                break;
            case 3:
                $this->arrayLocal[$a[0]][$a[1]][$a[2]] = [];
                break;
            case 4:
                $this->arrayLocal[$a[0]][$a[1]][$a[2]][$a[3]] = [];
                break;
            case 5:
                $this->arrayLocal[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]] = [];
                break;
            case 6:
                $this->arrayLocal[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]][$a[5]] = [];
                break;
            case 7:
                $this->arrayLocal[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]][$a[5]][$a[6]] = [];
                break;
            case 8:
                $this->arrayLocal[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]][$a[5]][$a[6]][$a[7]] = [];
                break;
            case 9:
                $this->arrayLocal[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]][$a[5]][$a[6]][$a[7]][$a[8]] = [];
                break;
            case 10:
                $this->arrayLocal[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]][$a[5]][$a[6]][$a[7]][$a[8]][$a[9]] = [];
                break;
            default:
                $this->arrayLocal[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]][$a[5]][$a[6]][$a[7]][$a[8]][$a[9]]["TRUNCATE"] = [];
                array_push($this->arrayLocal[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]][$a[5]][$a[6]][$a[7]][$a[8]][$a[9]]["TRUNCATE"], "Array Limit Exceeded");
        }
    }

    /**
     * @description Array Dig Layers
     * @return void
     */
    private function arrayDigLayers(): void
    {
        foreach ($this->arrayData as $k0 => $v0) {

            if (is_array($v0)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-0: [$k0]");
                }

                $this->resetArrayPush();
                $this->setArrayPush($k0);
                $this->setArrayLocal();
                $this->arrayDigLayer1($v0, [$k0]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("    => [$k0] = $v0");
                }

                $this->arrayLocal[$k0] = $v0;
                if ($this->checkInArray($k0)) {
                    $this->arrayLocal[$k0] = GenericTools::stringRepeat($v0, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer1
     * @param array $a0 #Mandatory
     * @param array $k0 #Mandatory
     * @return void
     */
    private function arrayDigLayer1(array $a0, array $k0): void
    {
        foreach ($a0 as $k1 => $v1) {

            if (is_array($v1)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-1: [$k1]");
                }

                $this->setArrayPush($k1);
                $this->setArrayLocal();
                $this->arrayDigLayer2($v1, [$k0[0], $k1]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("    => [$k0[0]][$k1] = $v1");
                }

                $this->arrayLocal[$k0[0]][$k1] = $v1;
                if ($this->checkInArray($k1)) {
                    $this->arrayLocal[$k0[0]][$k1] = GenericTools::stringRepeat($v1, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer2
     * @param array $a1 #Mandatory
     * @param array $k1 #Mandatory
     * @return void
     */
    private function arrayDigLayer2(array $a1, array $k1): void
    {
        foreach ($a1 as $k2 => $v2) {

            if (is_array($v2)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-2: [$k2]");
                }

                $this->setArrayPush($k2);
                $this->setArrayLocal();
                $this->arrayDigLayer3($v2, [$k1[0], $k1[1], $k2]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("    => [$k1[0]][$k1[1]][$k2] = $v2");
                }

                $this->arrayLocal[$k1[0]][$k1[1]][$k2] = $v2;
                if ($this->checkInArray($k2)) {
                    $this->arrayLocal[$k1[0]][$k1[1]][$k2] = GenericTools::stringRepeat($v2, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer3
     * @param array $a2 #Mandatory
     * @param array $k2 #Mandatory
     * @return void
     */
    private function arrayDigLayer3(array $a2, array $k2): void
    {
        foreach ($a2 as $k3 => $v3) {

            if (is_array($v3)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-3: [$k3]");
                }

                $this->setArrayPush($k3);
                $this->setArrayLocal();
                $this->arrayDigLayer4($v3, [$k2[0], $k2[1], $k2[2], $k3]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("   => [$k2[0]][$k2[1]][$k2[2]][$k3] = $v3");
                }

                $this->arrayLocal[$k2[0]][$k2[1]][$k2[2]][$k3] = $v3;
                if ($this->checkInArray($k3)) {
                    $this->arrayLocal[$k2[0]][$k2[1]][$k2[2]][$k3] = GenericTools::stringRepeat($v3, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer4
     * @param array $a3 #Mandatory
     * @param array $k3 #Mandatory
     * @return void
     */
    private function arrayDigLayer4(array $a3, array $k3): void
    {
        foreach ($a3 as $k4 => $v4) {

            if (is_array($v4)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-4: [$k4]");
                }

                $this->setArrayPush($k4);
                $this->setArrayLocal();
                $this->arrayDigLayer5($v4, [$k3[0], $k3[1], $k3[2], $k3[3], $k4]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("   => [$k3[0]][$k3[1]][$k3[2]][$k3[3]][$k4] = $v4");
                }

                $this->arrayLocal[$k3[0]][$k3[1]][$k3[2]][$k3[3]][$k4] = $v4;
                if ($this->checkInArray($k4)) {
                    $this->arrayLocal[$k3[0]][$k3[1]][$k3[2]][$k3[3]][$k4] = GenericTools::stringRepeat($v4, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer5
     * @param array $a4 #Mandatory
     * @param array $k4 #Mandatory
     * @return void
     */
    private function arrayDigLayer5(array $a4, array $k4): void
    {
        foreach ($a4 as $k5 => $v5) {

            if (is_array($v5)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-5: [$k5]");
                }

                $this->setArrayPush($k5);
                $this->setArrayLocal();
                $this->arrayDigLayer6($v5, [$k4[0], $k4[1], $k4[2], $k4[3], $k4[4], $k5]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("   => [$k4[0]][$k4[1]][$k4[2]][$k4[3]][$k4[4]][$k5] = $v5");
                }

                $this->arrayLocal[$k4[0]][$k4[1]][$k4[2]][$k4[3]][$k4[4]][$k5] = $v5;
                if ($this->checkInArray($k5)) {
                    $this->arrayLocal[$k4[0]][$k4[1]][$k4[2]][$k4[3]][$k4[4]][$k5] = GenericTools::stringRepeat($v5, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer6
     * @param array $a5 #Mandatory
     * @param array $k5 #Mandatory
     * @return void
     */
    private function arrayDigLayer6(array $a5, array $k5): void
    {
        foreach ($a5 as $k6 => $v6) {

            if (is_array($v6)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-6: [$k6]");
                }

                $this->setArrayPush($k6);
                $this->setArrayLocal();
                $this->arrayDigLayer7($v6, [$k5[0], $k5[1], $k5[2], $k5[3], $k5[4], $k5[5], $k6]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("   => [$k5[0]][$k5[1]][$k5[2]][$k5[3]][$k5[4]][$k5[5]][$k6] = $v6");
                }

                $this->arrayLocal[$k5[0]][$k5[1]][$k5[2]][$k5[3]][$k5[4]][$k5[5]][$k6] = $v6;
                if ($this->checkInArray($k6)) {
                    $this->arrayLocal[$k5[0]][$k5[1]][$k5[2]][$k5[3]][$k5[4]][$k5[5]][$k6] = GenericTools::stringRepeat($v6, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer7
     * @param array $a6 #Mandatory
     * @param array $k6 #Mandatory
     * @return void
     */
    private function arrayDigLayer7(array $a6, array $k6): void
    {
        foreach ($a6 as $k7 => $v7) {

            if (is_array($v7)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-7: [$k7]");
                }

                $this->setArrayPush($k7);
                $this->setArrayLocal();
                $this->arrayDigLayer8($v7, [$k6[0], $k6[1], $k6[2], $k6[3], $k6[4], $k6[5], $k6[6], $k7]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("   => [$k6[0]][$k6[1]][$k6[2]][$k6[3]][$k6[4]][$k6[5]][$k6[6]][$k7] = $v7");
                }

                $this->arrayLocal[$k6[0]][$k6[1]][$k6[2]][$k6[3]][$k6[4]][$k6[5]][$k6[6]][$k7] = $v7;
                if ($this->checkInArray($k7)) {
                    $this->arrayLocal[$k6[0]][$k6[1]][$k6[2]][$k6[3]][$k6[4]][$k6[5]][$k6[6]][$k7] = GenericTools::stringRepeat($v7, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer8
     * @param array $a7 #Mandatory
     * @param array $k7 #Mandatory
     * @return void
     */
    private function arrayDigLayer8(array $a7, array $k7): void
    {
        foreach ($a7 as $k8 => $v8) {

            if (is_array($v8)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-8: [$k8]");
                }

                $this->setArrayPush($k8);
                $this->setArrayLocal();
                $this->arrayDigLayer9($v8, [$k7[0], $k7[1], $k7[2], $k7[3], $k7[4], $k7[5], $k7[6], $k7[7], $k8]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("   => [$k7[0]][$k7[1]][$k7[2]][$k7[3]][$k7[4]][$k7[5]][$k7[6]][$k7[7]][$k8] = $v8");
                }

                $this->arrayLocal[$k7[0]][$k7[1]][$k7[2]][$k7[3]][$k7[4]][$k7[5]][$k7[6]][$k7[7]][$k8] = $v8;
                if ($this->checkInArray($k8)) {
                    $this->arrayLocal[$k7[0]][$k7[1]][$k7[2]][$k7[3]][$k7[4]][$k7[5]][$k7[6]][$k7[7]][$k8] = GenericTools::stringRepeat($v8, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer9
     * @param array $a8 #Mandatory
     * @param array $k8 #Mandatory
     * @return void
     */
    private function arrayDigLayer9(array $a8, array $k8): void
    {
        foreach ($a8 as $k9 => $v9) {

            if (is_array($v9)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-9: [$k9]");
                }

                $this->setArrayPush($k9);
                $this->setArrayLocal();
                $this->arrayDigLayerFinish($v9, [$k8[0], $k8[1], $k8[2], $k8[3], $k8[4], $k8[5], $k8[6], $k8[7], $k8[8], $k9]);

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("   => [$k8[0]][$k8[1]][$k8[2]][$k8[3]][$k8[4]][$k8[5]][$k8[6]][$k8[7]][$k8[8]][$k9] = $v9");
                }

                $this->arrayLocal[$k8[0]][$k8[1]][$k8[2]][$k8[3]][$k8[4]][$k8[5]][$k8[6]][$k8[7]][$k8[8]][$k9] = $v9;
                if ($this->checkInArray($k9)) {
                    $this->arrayLocal[$k8[0]][$k8[1]][$k8[2]][$k8[3]][$k8[4]][$k8[5]][$k8[6]][$k8[7]][$k8[8]][$k9] = GenericTools::stringRepeat($v9, "*");
                }
            }
        }
    }

    /**
     * @description Array Dig Layer9
     * @param array $a9 #Mandatory
     * @param array $k9 #Mandatory
     * @return void
     */
    private function arrayDigLayerFinish(array $a9, array $k9): void
    {
        foreach ($a9 as $k10 => $v10) {

            if (is_array($v10)) {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("Layer-10: [$k10]");
                }

                $this->setArrayPush($k10);
                $this->setArrayLocal();

                /**
                 * Ops! Here is the limit 10 to array digger
                 */

            } else {

                /*DEBUG*/
                if ($this->arrayProcDebug) {
                    pr("   => [$k9[0]][$k9[1]][$k9[2]][$k9[3]][$k9[4]][$k9[5]][$k9[6]][$k9[7]][$k9[8]][$k9[9]][$k10] = $v10");
                }

                $this->arrayLocal[$k9[0]][$k9[1]][$k9[2]][$k9[3]][$k9[4]][$k9[5]][$k9[6]][$k9[7]][$k9[8]][$k9[9]][$k10] = $v10;
                if ($this->checkInArray($k10)) {
                    $this->arrayLocal[$k9[0]][$k9[1]][$k9[2]][$k9[3]][$k9[4]][$k9[5]][$k9[6]][$k9[7]][$k9[8]][$k9[9]][$k10] = GenericTools::stringRepeat($v10, "*");
                }
            }
        }
    }

    /**
     * @description Array Value Mask
     * @return array
     */
    public function arrayValueMask(): array
    {
        if (!$this->arrayData || count($this->arrayData) == 0) {
            return [];
        }

        $this->arrayDigLayers();

        /*DEBUG*/
        if ($this->arrayProcDebug) {
            pr("ARRAY-LOCAL");
            pr($this->arrayLocal);
            prd("DONE!!!!");
        }

        return $this->arrayLocal;
    }
}
