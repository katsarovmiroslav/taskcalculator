<?php

namespace Core\Exchange;
 
use Core\Currencies\Currencies;
use Core\Exception\UndefinedException;

class Exchange
{
    
    /**
    * @param string $currency
    * @throws UndefinedException
    * @return float
    */
    public function getCurrencyRate(string $currency) : float
    {
        $rates = Currencies::getCurrencies();

        if (isset($rates[$currency])) {
            return $rates[$currency];
        }

        throw new UndefinedException(
            sprintf('Currency "%s" is not found.', $currency)
        );
    }

    /**
    * Currency exchange rate change.
    *
    * @param float $amount
    * @param string $toCurrency
    * @param string $fromCurrency 
    * @return float
    */
    public function calculateRateCash($amount, $toCurrency, $fromCurrency = null) : float
    {
        $rates = Currencies::getCurrencies();
        
        if(!isset($fromCurrency)) {
            $fromCurrency = Currencies::getBaseCurrency();
        }
		
        if (Currencies::getBaseCurrency() !== $fromCurrency) {
            $amount = $amount / $rates[$fromCurrency]; 
        }

        if ($toCurrency === Currencies::getBaseCurrency()) {
            return $amount;
        }

        return $amount * $rates[$toCurrency]; 
    }
}
