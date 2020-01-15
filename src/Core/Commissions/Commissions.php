<?php

namespace Core\Commissions;
 
use Core\Exchange\Exchange;
use Core\Currencies\Currencies;
use Core\User\User;

class Commissions
{
    const MAXIMUM_CASH_IN_COMMISSION_AMOUNT_EURO = 5;
    const MINIMUM_CASH_OUT_COMMISSION_AMOUNT_EURO = 0.5;

    const CASH_OUT_COMMISSION_PERCENTAGE = 0.3;
    const CASH_IN_COMMISSION_PERCENTAGE = 0.03;

    const WEEKLY_LIMIT_FOR_DISCOUNT = 3;
    const WEEKLY_LIMIT_AMOUNT_DISCOUNT = 1000;
	
    /**
    * @var Array
    */
    private $allCommissions = []; 
      
    /**
    * Fee for cashIn operation.
    * @param array $rowDataOperation
    * @return float
    */  
    public function cashIn(array $rowDataOperation):float 
    { 
		 
        $commission = $rowDataOperation['amount'] / 100 * self::CASH_IN_COMMISSION_PERCENTAGE;
        $commissionInEur = Exchange::calculateRateCash($commission, $rowDataOperation['currency']);
		 
        if ($commissionInEur > self::MAXIMUM_CASH_IN_COMMISSION_AMOUNT_EURO) {
            $commissionInEur = self::MAXIMUM_CASH_IN_COMMISSION_AMOUNT_EURO;
        }
		
        return $commissionInEur;
    }
	
    /**
    * Fee for companies in towing operation.
    * @param array $rowDataOperation
    * @return float
    */ 
    public function cashOutLegal(array $rowDataOperation):float 
    { 
	 
        $commission = $rowDataOperation['amount'] / 100 * self::CASH_OUT_COMMISSION_PERCENTAGE;
        $commissionInEur = Exchange::calculateRateCash($commission, $rowDataOperation['currency']);

        if ($commissionInEur <= self::MINIMUM_CASH_OUT_COMMISSION_AMOUNT_EURO) {
            $commission = Exchange::calculateRateCash(self::MINIMUM_CASH_OUT_COMMISSION_AMOUNT_EURO, Currencies::getBaseCurrency(), $rowDataOperation['currency']); 
        } 
		 
        return $commission;
    }
	 
    /**
    * Commission for individuals. The towing operation.
    * @param array $rowDataOperation
    * @return float
    */
    public function cashOutNatural(array $rowDataOperation, $userObj):float 
    { 
		   
        $userObj->setUserOperation(
            $rowDataOperation['idUser'], 
            date_format($rowDataOperation['date'], DATE_FORMAT), 
            $rowDataOperation['numOperatin'],
            Exchange::calculateRateCash($rowDataOperation['amount'], DEFAULT_CURRENCY, $rowDataOperation['currency']),
            $rowDataOperation['weekNumber']
        );
		
        $getAllUsersOperation = $userObj->getUserOperation(); 
        $getUserOperations = $getAllUsersOperation[$rowDataOperation['idUser']];
		
        if(isset($getUserOperations)) {
			  
            foreach ($getUserOperations as $week => $operationsData) {
                $index = 0; 
                $totalAmount = 0;  
				  
                foreach ($operationsData as $operationId => $operationData) { 
                    $index += 1;
					
                    if($index <= 3 AND $operationId === $rowDataOperation['numOperatin']) {
					 
                        $totalAmountOperation = array_sum(array_column($getUserOperations[$week], 'amount'));
                        if(
                            $totalAmountOperation > self::WEEKLY_LIMIT_AMOUNT_DISCOUNT && 
                            ($totalAmountOperation-self::WEEKLY_LIMIT_AMOUNT_DISCOUNT) <= $operationData['amount']
                        ){
						 
                            $amountCommission = Exchange::calculateRateCash(($totalAmountOperation-self::WEEKLY_LIMIT_AMOUNT_DISCOUNT), $rowDataOperation['currency']); 
                            $commission = $amountCommission / 100 * self::CASH_OUT_COMMISSION_PERCENTAGE; 
					
                            return $commission; 
                        } elseif(
                            $totalAmountOperation > self::WEEKLY_LIMIT_AMOUNT_DISCOUNT && 
                            ($totalAmountOperation-self::WEEKLY_LIMIT_AMOUNT_DISCOUNT) >= $operationData['amount']
                        ) {
						 
                            $amountCommission = Exchange::calculateRateCash($operationData['amount'], $rowDataOperation['currency']);
                            $commission = $amountCommission / 100 * self::CASH_OUT_COMMISSION_PERCENTAGE; 
					
                            return $commission;
                        } elseif ($totalAmount <= self::WEEKLY_LIMIT_AMOUNT_DISCOUNT) {
                            $commission = 0; 
						 	
                            return $commission;
                        }
                    } elseif($operationId === $rowDataOperation['numOperatin']) {
                        $commission = $rowDataOperation['amount'] / 100 * self::CASH_OUT_COMMISSION_PERCENTAGE; 
					
                        return $commission;
                    }
                }   
            }
        } 	
    }
	
    /** 
    * @param int $operationId
    * @param float $commission 
    */
    public function save($operationId, $commission):void 
    {  
        $comissionFormat = $this->ceiling($commission, 2);
        if($comissionFormat > 1000) {
            $comissionFormat = ceil($comissionFormat);
        }
        $this->allCommissions[$operationId] = number_format((float)$comissionFormat, 2, '.', '');
    }
	 
    /** 
    * @return array
    */
    public function getCommission():array 
    { 
        return $this->allCommissions; 
    }
	
    /**
    * Formatting numbers
    * @param float $value
    * @param int $precision
    * @return float
    */
    private function ceiling($value, int $precision = 0) : float
    {
        $pow = pow ( 10, $precision ); 
        return ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow; 
    } 
}
