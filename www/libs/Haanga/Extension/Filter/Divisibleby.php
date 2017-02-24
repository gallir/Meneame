<?php

class Haanga_Extension_Filter_Divisibleby
{
    public static function main($number, $divisible_by)
    {
        return ($number % $divisible_by) == 0;
    }
}
