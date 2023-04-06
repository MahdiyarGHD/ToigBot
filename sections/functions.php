<?php

function filter($str) {
    if (gettype($str) != 'string') 
    {
        return $str;
    }
    return strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $str));
}

function random_float_in_range($min, $max) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

function ends_with($string, $endString) { 
    $len = strlen($endString); 
    if ($len == 0) { 
        return true; 
    } 
    return (substr($string, -$len) === $endString); 
} 