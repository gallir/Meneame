<?php

class Obj 
{
    public function endpoint() {
        return $this->method();
    }

    public function method(){
        return array('a', 'b');
    }
}
