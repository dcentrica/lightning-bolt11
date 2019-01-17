<?php

declare(strict_types=1);

/**
 * @author  Russell Michell for Dcentrica 2019 <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

namespace Dcentrica\Bolt11;

/**
 * Takes userland payment data and encodes it to the BOLT-11 standard, suited for
 * display for example in QR codes used in Lightning Network Bitcoin and Litecoin
 * payments.
 * 
 * Ported to PHP from: https://github.com/rustyrussell/lightning-payencode/blob/master/lnaddr.py
 * 
 * @see https://github.com/bitcoinjs/bolt11
 * @see https://github.com/rustyrussell/lightning-payencode and port that to PHP
 * @see https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md
 * @see https://medium.com/lightwork/development-tooling-for-invoices-in-lightning-network-applications-cf3d194dc2bb
 * 
 * Example output can be seen here: https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md#examples
 * 
 * The LNAddr class contains all the subroutines for massaging userland data into a coherent whole.
 * 
 * @todo Run the python code, and ensure we know what is supposed to be returned from each method
 */
class LNAddr
{
    /**
     * A map of classical and witness address prefixes.
     * 
     * @var array
     */
    private static $base58_prefix_map = [
        'bc' => [0, 5],
        'tb' => [111, 196],
    ];
    
    /**
     * @var string
     */
    protected $paymentHash;
    protected $amount;
    protected $currency;
    protected $pubKey;
    protected $signature;
    
    /**
     * @var array
     */
    protected $tags;
    protected $unknownTags;
    
    /**
     * @var int
     */
    protected $date;
    
    /**
     * Construct a BOLT-11 Lightning Payment suited for display in a QR code.
     * 
     * @param  string $paymentHash
     * @param  string $amount
     * @param  string $currency
     * @param  array  $tags
     * @param  int    $date
     * @return void
     */
    public function __construct(
            string $paymentHash = '',
            string $amount = '',
            string $currency = 'bc',
            array $tags = [],
            int $date = null)
    {
        $this->paymentHash = $paymentHash ?: '';
        $this->amount = $amount ?: '';
        $this->currency = $currency ?: '';
        $this->tags = $tags ?: [];
        $this->date = $date ?: time();
        $this->pubKey = '';
        $this->signature = '';
    }
    
    /**
     * BOLT #11:
     * 
     * A writer MUST encode $amount as a positive decimal integer with no
     * leading zeroes, SHOULD use the shortest representation possible.
     * 
     * @param  int $amount
     * @return string
     */
    public static function shorten_amount(int $amount) : string
    {
        // Given an amount in bitcoin, shorten it
        // Convert to pico initially
        $amount = pow($amount * 10, 12);
        $units = ['p', 'n', 'u', 'm'];
        
        foreach ($units as $unit) {
            if (($amount % 1000) === 0) {
                return sprintf('%d%s', floor($amount / 1000), $unit);
            }
        }
    }
    
    /**
     * BOLT #11:
     * 
     * The following `multiplier` letters are defined:
     * 
     * - `m` (milli): multiply by 0.001
     * - `u` (micro): multiply by 0.000001
     * - `n` (nano): multiply by 0.000000001
     * - `p` (pico): multiply by 0.000000000001
     * 
     * @param  string "Encoded" $amount
     * @return int
     */
    public static function unshorten_amount(string $amount) : int
    {
        
    }
    
    /**
     * Bech32 spits out array of 5-bit values.  Shim here.
     * 
     * @param  array $arr
     * @return string
     */
    public static function u5_to_bitarray(array $arr) : string
    {
        
    }
    
    /**
     * Bech32 spits out array of 5-bit values.  Shim here.
     * 
     * @param  array $arr
     * @return string
     */
    public static function bitarray_to_u5(array $arr) : string
    {
        
    }
    
    /**
     * @param  int    $fallback
     * @param  string $currency
     * @return string Binary (May have to be an array of bytes)
     */
    public static function encode_fallback(int $fallback, string $currency) : string
    {
        
    }
    
    /**
     * @param  int    $fallback
     * @param  string $currency
     * @return int
     */
    public static function parse_fallback(int $fallback, string $currency) : int
    {
        
    }
    
    /**
     * @param  string $currency
     * @param  string $prefix
     * @return bool
     */
    public static function is_p2pkh(string $currency, string $prefix) : bool
    {
        
    }
    
    /**
     * @param  string $currency
     * @param  string $prefix
     * @return bool
     */
    public static function is_p2sh(string $currency, string $prefix) : bool
    {
        
    }
    
    /**
     * "Tagged" field containing BitArray
     * 
     * @param  string $char
     * @return string Binary (May have to be an array of bytes)
     */
    public static function tagged(string $char) : string
    {
        
    }
    
    /**
     * @param string $char
     * @return string Binary (May have to be an array of bytes)
     */
    public static function tagged_bytes(string $char) : string
    {
        
    }
    
    /**
     * @param  array $barray An array of bytes
     * @return array
     */
    public static function trim_to_bytes(array $barray) : array
    {
        
    }
    
    /**
     * Try to pull out tagged data: returns an array of: tag, tagged-data and remainder.
     * 
     * @param  string $stream
     * @return array
     */
    public static function pull_tagged(string $stream) : array
    {
        
    }
    
    /**
     * @param  string $addr
     * @param  string $privKey
     * @return string A Bech32 encoded string
     */
    public static function lnencode(string $addr, string $privKey) : string
    {
        
    }
    
    /**
     * @param  string $encoded
     * @param  bool   $verbose
     * @return string A decoded ech32 string
     */
    public static function lndecode(string $encoded, $verbose = false) : string
    {
        
    }
    
    /**
     * Return a stringy representation of the compiled address.
     * 
     * @return string
     * @see https://github.com/rustyrussell/lightning-payencode/blob/master/lnaddr.py line 244
     */
    public function __toString() : string
    {
        return '';
    }
    
}
