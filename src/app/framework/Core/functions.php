<?php

/**
 * @return bool
 */
function isFloat($number)
{
    return ($number == (string)(float) $number);
}

/**
 * @param array $array
 * @return bool
 */
function isAssoc(array $array)
{
    return (bool) count(array_filter(array_keys($array), "is_string"));
}

