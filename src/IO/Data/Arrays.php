<?php

namespace TorneLIB\IO\Data;

use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Utils\Security;

class Arrays
{
    /**
     * Convert object to a data object. Formerly known as a repair tool for __PHP_Incomplete_Class.
     *
     * @param array $objectArray
     * @return object|array|mixed
     * @throws ExceptionHandler
     * @since 6.0.0
     */
    public function arrayObjectToStdClass($objectArray = [])
    {
        /**
         * If json_decode and json_encode exists as function, do it the simple way.
         * http://php.net/manual/en/function.json-encode.php
         */
        if (Security::getCurrentFunctionState('json_decode', false) &&
            Security::getCurrentFunctionState('json_encode', false)) {
            return json_decode(json_encode($objectArray));
        }
        $newArray = null;
        if (is_array($objectArray) || is_object($objectArray)) {
            foreach ($objectArray as $itemKey => $itemValue) {
                if (is_array($itemValue)) {
                    $newArray[$itemKey] = (array)$this->arrayObjectToStdClass($itemValue);
                } elseif (is_object($itemValue)) {
                    $newArray[$itemKey] = (object)(array)$this->arrayObjectToStdClass($itemValue);
                } else {
                    $newArray[$itemKey] = $itemValue;
                }
            }
        }

        return $newArray;
    }

    /**
     * Convert objects to arrays
     *
     * @param $arrObjData
     * @param array $arrSkipIndices
     * @return array
     * @since 6.0.0
     */
    public function objectsIntoArray($arrObjData, $arrSkipIndices = [])
    {
        $arrData = [];
        // if input is object, convert into array
        if (is_object($arrObjData)) {
            $arrObjData = get_object_vars($arrObjData);
        }
        if (is_array($arrObjData)) {
            foreach ($arrObjData as $index => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = $this->objectsIntoArray($value, $arrSkipIndices); // recursive call
                }
                if (@in_array($index, $arrSkipIndices)) {
                    continue;
                }
                $arrData[$index] = $value;
            }
        }

        return $arrData;
    }

    /**
     * @param array $arrayData
     * @return bool
     * @since 6.0.2
     */
    public function isAssoc(array $arrayData)
    {
        if ([] === $arrayData) {
            return false;
        }

        return array_keys($arrayData) !== range(0, count($arrayData) - 1);
    }

    /**
     * Pair data into array.
     *
     * Example: http://api.tornevall.net/2.0/endpoints/getendpointmethods/endpoint/test/
     * As a pair, this URI looks like:
     * /endpoints/getendpointmethods/ - Endpoint+Verb (key=>value)
     * /endpoint/test/                - Key=>Value (array('endpoint'=>'test'))
     *
     * @param array $arrayArgs
     * @return array
     * @since 6.1.2
     */
    public function getArrayPair($arrayArgs = [])
    {
        $pairedArray = [];
        for ($keyCount = 0; $keyCount < count($arrayArgs); $keyCount = $keyCount + 2) {
            /**
             * Silently suppress things that does not exist
             */
            if (!isset($pairedArray[$arrayArgs[$keyCount]])) {
                // Repair possible dual slashes.
                if (empty($arrayArgs[$keyCount])) {
                    $keyCount--;
                    continue;
                }
                $pairedArray[$arrayArgs[$keyCount]] = null;
            }
            if (!isset($arrayArgs[$keyCount + 1])) {
                $arrayArgs[$keyCount + 1] = null;
            }

            /**
             * Start the pairing
             */
            $pairedArray[$arrayArgs[$keyCount]] = (!empty($arrayArgs[$keyCount + 1]) &&
            isset($arrayArgs[$keyCount + 1]) ?
                $arrayArgs[$keyCount + 1] :
                ""
            );
        }
        return $pairedArray;
    }
}
