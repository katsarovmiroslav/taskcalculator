<?php

namespace Core\Currencies;

class Currencies
{
    /**
    * @return array
    */
    public function getCurrencies() : array
    {
        return [
            'EUR' => 1,
            'USD' => 1.1497,
            'JPY' => 129.53
        ];
    }

    /**
    * @return string
    */
    public function getBaseCurrency() : string
    {
        return DEFAULT_CURRENCY;
    }
}
