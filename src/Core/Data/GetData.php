<?php

namespace Core\Data;

class GetData
{
    /**
    * @param array $argv 
    */
    public function inputData(array $argv) : array
    {  
        if(isset($argv[1])) {
            return array_map('str_getcsv', file($argv[1])); 
        } else {
            return self::otherInputData();
        } 
    }

    public function otherInputData() : array
    {  
        return array();
    }
}
