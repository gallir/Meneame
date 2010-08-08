<?php

class Haanga_Extension_Filter_Date
{
    function generator($compiler, $args)
    {
        return hexec('date', $args[1], $args[0]);
    }
}
    

