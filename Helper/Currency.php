<?php

/**
 * OnPay Magento2 module
 *
 * @category  Payment_Method
 * @package   OnPay_Magento2
 * @copyright OnPay
 *
 * @magento-module
 * Plugin Name: OnPay Magento2
 * Plugin URI: https://onpay.io
 * Description: Collect payments using OnPay.io as PSP
 * Author URI: https://onpay.io
 */

declare(strict_types=1);

namespace OnPay\Magento2\Helper;

use Alcohol\ISO4217;

class Currency
{
    /**
     * @var ISO4217 $converter 
     */
    private $converter;

    public function __construct()
    {
        $this->converter = new ISO4217();
    }

    /**
     * @param  int    $amount
     * @param  string $currency
     * @param  string $decimalSeparator
     * @return int|mixed|string
     */
    public function minorToMajor($amount, $currency, $decimalSeparator = '.')
    {
        $currencyConverter = (object)$this->converter->getByNumeric($currency);
        $amount = strval($amount);
        if ($currencyConverter->exp > 0) {
            $newAmount = str_pad($amount, $currencyConverter->exp + 1, '0', STR_PAD_LEFT);
            return substr_replace($newAmount, $decimalSeparator, (0 - $currencyConverter->exp), 0);
        }
        return $amount;
    }

    /**
     * @param  string $amount
     * @param  string $currency
     * @param  string $separator
     * @return int
     */
    public function majorToMinor($amount, $currency, $separator)
    {
        $currencyConverter = (object)$this->converter->getByAlpha3($currency);
        $fraction = '';
        for ($i = 0; $i < $currencyConverter->exp; $i++) {
            $fraction .= '0';
        }
        $amountArr = explode($separator, $amount);
        $integer = $amountArr[0];
        if (array_key_exists(1, $amountArr)) {
            $amountFraction = substr($amountArr[1], 0, $currencyConverter->exp);
            $fraction = substr_replace($fraction, $amountFraction, 0, strlen($amountFraction));
        }
        return intval($integer . $fraction);
    }

    /**
     * @param  $numeric
     * @return object
     */
    public function fromNumeric($numeric)
    {
        // We'll add missing trailing zeroes.
        $numeric = (string)$numeric;
        $currencyNumeric = $numeric;
        if (1 === strlen($numeric)) {
            $currencyNumeric = '00' . $numeric;
        } else if (2 === strlen($numeric)) {
            $currencyNumeric = '0' . $numeric;
        }
        try {
            return (object)$this->converter->getByNumeric($currencyNumeric);
        } catch (\DomainException $e) {
            return null;
        } catch (\OutOfBoundsException $e) {
            return null;
        }
    }

    /**
     * @param  $alpha3
     * @return object
     */
    public function fromAlpha3($alpha3)
    {
        try {
            return (object)$this->converter->getByAlpha3($alpha3);
        } catch (\DomainException $e) {
            return null;
        } catch (\OutOfBoundsException $e) {
            return null;
        }
    }
}