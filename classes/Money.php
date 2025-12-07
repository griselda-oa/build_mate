<?php

declare(strict_types=1);

namespace App;

/**
 * Money/currency helper
 */
class Money
{
    /**
     * Format cents to currency string
     */
    public static function format(int $cents, string $currency = 'GHS'): string
    {
        $amount = $cents / 100;
        return $currency . ' ' . number_format($amount, 2);
    }
    
    /**
     * Convert USD to GHS
     */
    public static function usdToGhs(int $usdCents): int
    {
        $config = require __DIR__ . '/../settings/config.php';
        $rate = $config['currency']['usd_to_ghs_rate'];
        return (int)round($usdCents * $rate);
    }
    
    /**
     * Convert GHS to USD
     */
    public static function ghsToUsd(int $ghsCents): int
    {
        $config = require __DIR__ . '/../settings/config.php';
        $rate = $config['currency']['usd_to_ghs_rate'];
        return (int)round($ghsCents / $rate);
    }
    
    /**
     * Convert amount based on currency
     */
    public static function convert(int $cents, string $from, string $to): int
    {
        if ($from === $to) {
            return $cents;
        }
        
        if ($from === 'USD' && $to === 'GHS') {
            return self::usdToGhs($cents);
        }
        
        if ($from === 'GHS' && $to === 'USD') {
            return self::ghsToUsd($cents);
        }
        
        return $cents;
    }
}

