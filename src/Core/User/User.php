<?php

namespace Core\User;

class User
{ 
    /**
    * @var array
    */
    public $allUserOperation = [];
	
    /**
    * Array of data on operations by weeks and users.
    *
    * @param int $userId
    * @param string $date
    * @param int $operationId
    * @param float $amount
    * @param int $week
    * @return array
    */
    public function setUserOperation($userId, $date, $operationId, $amount, $week) : void
    { 
        $this->allUserOperation[$userId][$week][$operationId]['date'] = $date; 
        $this->allUserOperation[$userId][$week][$operationId]['amount'] = $amount; 
    }

    /**
    * @return array
    */
    public function getUserOperation(): array
    {
        return $this->allUserOperation;
        
    }
}