<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Core\Data\GetData;
use Core\User\User;
use Core\Operations\Operations;
use Core\Commissions\Commissions; 

$getData = new GetData();   
$user = new User();
$operations = new Operations($user);  
$commissions = new Commissions($user);   

$csvRowsData = $getData->inputData($argv);
 
//Data for operations and users
foreach ($csvRowsData as $index => $rowData) {
    $operationID = $index + 1;  
    $operations->operationData(
        $operationID,
        $rowData[1],
        $rowData[3],
        $rowData[2],
        $rowData[4],
        $rowData[5],
        $rowData[0],
        $commissions
    );
    $operations->operationCreate($operationID);
}

// Calculate commission.
foreach ($commissions->getCommission() as $operation) { 
    fwrite(STDOUT, $operation . PHP_EOL);
}






