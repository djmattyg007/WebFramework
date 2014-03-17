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
 * @param array $array
 * @return bool
 */
function isAssoc(array $array)
{
    return (bool) count(array_filter(array_keys($array), "is_string"));
}
