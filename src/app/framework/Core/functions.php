<?php

/**
 * Check to see whether or not a string is a valid floating-point number.
 * If you convert a floating-point number in a string-type variable to a
 * float-type variable, and then back to a string, it should equal exactly
 * what it was before the original conversion.
 *
 * @param string $number
 * @return bool
 */
function isFloat($number)
{
    return ($number == (string)(float) $number);
}

/**
 * Check to see whether or not the keys in an array are of type string or of
 * type integer. If there is at least one key that is of type string, the array
 * is considered to be an associative array.
 *
 * This function has a Big-O time complexity of O(2N).
 *
 * @param array $array
 * @return bool
 */
function isAssoc(array $array)
{
    return (bool) count(array_filter(array_keys($array), "is_string"));
}

/**
 * Iterate over all keys in the array and remove any that are non-numeric.
 *
 * This function has a Big-O time complexity of O(N).
 *
 * @param array $array
 * @return array
 */
function stripAssocKeys(array $array)
{
    foreach ($array as $key => $value) {
        if (!is_int($key)) {
            unset($array[$key]);
        }
    }
    return $array;
}

/**
 * Iterate over all keys in the array and remove any that are numeric.
 *
 * This function has a Big-O time complexity of O(N).
 *
 * @param array $array
 * @return array
 */
function stripNumericKeys(array $array)
{
    foreach ($array as $key => $value) {
        if (is_int($key)) {
            unset($array[$key]);
        }
    }
    return $array;
}

