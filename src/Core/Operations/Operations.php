<?php

namespace Core\Operations;

use Core\Commissions\Commissions;
use Core\User\User;

class Operations
{
    /**
    * @var array
    */  
    public $operationAll = [];
	
    /**
    * @var User\User object
    */ 
    private $userObj;
 
    public function __construct(User $user)
    {	
        $this->userObj = $user; 
    }
	
    /**
    * Input array of operation data.
    *
    * @return array
    */
    public function operationData(
        int $numOperatin,
        int $idUser,
        string $operationType,
        string $userType,
        string $amount,
        string $currency,
        string $date,
        Commissions $commissions
    ) : void {
	   
        $formatDate = \DateTime::createFromFormat(DATE_FORMAT, $date);
 
        $this->operationAll[$numOperatin]['numOperatin'] = $numOperatin;
        $this->operationAll[$numOperatin]['idUser'] = $idUser;
        $this->operationAll[$numOperatin]['operationType'] = $operationType;
        $this->operationAll[$numOperatin]['userType'] = $userType;
        $this->operationAll[$numOperatin]['amount'] = $amount;
        $this->operationAll[$numOperatin]['currency'] = $currency;
        $this->operationAll[$numOperatin]['objCommissions'] = $commissions; 
        $this->operationAll[$numOperatin]['date'] = $formatDate;
        $this->operationAll[$numOperatin]['startWeekMonday'] = $this->startWeekMonday($formatDate);
        $this->operationAll[$numOperatin]['endWeekSunday'] = $this->endWeekSunday($formatDate);
        $this->operationAll[$numOperatin]['weekNumber'] = $this->weekNumber($this->endWeekSunday($formatDate));
    }

    /**
    * Commissions based on the type of operation.
    */
    public function operationCreate(
        int $id
    ) : void
    {
        $operationRow = $this->operationAll[$id];
		
        if($operationRow['operationType'] === 'cash_in') {
            $operationCommissions = Commissions::cashIn($operationRow); 
        } elseif ($operationRow['operationType'] === 'cash_out' && $operationRow['userType'] === 'natural') { 
            $operationCommissions = Commissions::cashOutNatural($operationRow, $this->userObj); 
        } elseif ($operationRow['operationType'] === 'cash_out' && $operationRow['userType'] === 'legal') {	
            $operationCommissions = Commissions::cashOutLegal($operationRow);
        }
		     
        $operationRow['objCommissions']->save($id, $operationCommissions);
    }
	
    /**
    * @param \DateTime $date
    * @return \DateTime
    */
    public function startWeekMonday(\DateTime $date) : \DateTime
    {
        $weekDay = (int)$date->format('w');
        $weekDay = $weekDay === 0 ? 7 : $weekDay;
		
        $monday = clone $date;

        return $monday->modify(
            sprintf('-%s day', $weekDay - 1)
        );
    }
	
    /**
    * @param \DateTime $date
    * @return \DateTime
    */
    public function endWeekSunday(\DateTime $date) : \DateTime
    {
        $weekDay = (int)$date->format('w');
        $weekDay = $weekDay === 0 ? 7 : $weekDay;

        $sunday = clone $date;

        return $sunday->modify(
            sprintf('+%s day', 7 - $weekDay)
        );
    }
	
    /**
    * @param \DateTime $date
    * @return int
    */
    public function weekNumber(\DateTime $date)
    {   
        $timestamp = strtotime($date->format('Y-m-d')); 
        $weekNumber = date('Y', $timestamp).idate('W', $timestamp);	 
        return $weekNumber;
    }
}
 