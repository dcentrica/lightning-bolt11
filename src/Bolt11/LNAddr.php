<?php

//declare(strict_types=1);

/**
 * @author  Russell Michell for Dcentrica 2019 <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

namespace Dcentrica\Bolt11;

use Decimal\Decimal;
use Dcentrica\Bolt11\Base58;

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
     *
     * @var string
     */
    private static $charset = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
    
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
     * Shorten an amount in Bitcoin.
     * 
     * A writer MUST encode $amount as a positive decimal integer with no
     * leading zeroes.
     * A writer SHOULD use the shortest representation possible.
     * 
     * @param  Decimal $decimal
     * @return string
     */
    public static function shorten_amount(Decimal $amount) : string
    {
        $amount = $amount->toString();
        $amount = (int) ($amount * 10 ** 12);
        $units = ['p', 'n', 'u', 'm', ''];
        
        foreach ($units as $unit) {
            if (($amount % 1000) == 0) {
                $amount = floor($amount / 1000);
            } else {
                break;
            }
        }
        
        return $amount . $unit;
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
     * @throws InvalidArgumentException
     */
    public static function unshorten_amount(string $amount) : int
    {
        $units = [
            'p' => (10 ** 12),
            'n' => (10 ** 9),
            'u' => (10 ** 6),
            'm' => (10 ** 3),
        ];
        $sunits = implode('|', array_keys($units));
        $unit = $amount[-1];
        
        // BOLT #11:
        // A reader SHOULD fail if `amount` contains a non-digit, or is followed by
        // anything except a `multiplier` in the table above.
        if (!preg_match("#\d+($sunits)?#", $amount)) {
            throw new \InvalidArgumentException(sprintf('Invalid amount: %s ', $amount));
        }

        if (in_array($unit, array_keys($units))) {
            return (new Decimal(substr($amount, 0, -1)))->toInt() / $units[$unit];
        } else {
            return (new Decimal($amount))->toInt();
        }
    }
    
    /**
     * Bech32 spits out array of 5-bit values. Shim here.
     * 
     * @param  int[]  $arr An array of ints.
     * @return array
     * @todo
     * @see Bitwasp\Bech32\convertBits()
     */
    public static function u5_to_bitarray(array $arr) : array
    {
        
    }
    
    /**
     * Bech32 spits out an array of 5-bit values. Shim here.
     * 
     * @param  int[]  $arr An array of ints.
     * @return array
     * @todo
     * @see Bitwasp\Bech32\convertBits()
     */
    public static function bitarray_to_u5(array $arr) : array
    {
        
    }
    
    /**
     * Encode all supported fallback addresses.
     * 
     * @param  int       $fallback
     * @param  string    $currency
     * @return string    Binary (May have to be an array of bytes)
     * @throws Exception
     */
    public static function encode_fallback(int $fallback, string $currency) : string
    {
        if ($currency === 'bc' || $currency === 'tb') {
            list($fbhrp, $witness) = BitWasp\Bech32\decode($fallback);
        
            if ($fbhrp && $witness) {
                if ($fbhrp != $currency) {
                    throw new \Exception(sprintf('Not a bech32 address for the currency %s', $currency));
                }

                $wver = $witness[0];

                if ($wver > 16) {
                    throw new \Exception(sprintf('Invalid witness version %s', $witness[0]));
                }

                $wprog = self::u5_to_bitarray(substr($witness, 1, strlen($witness)));
            } else {
                // An LN payment request with a BTC fallback is encoded at the tag: "f"
                $addr = (new Base58())->checkDecode($fallback, 'little');

                // Is script Pay2PublicKeyHash
                if (self::is_p2pkh($currency, $addr[0])) {
                    $wver = 17;
                // Is script Pay2ScriptHash
                } elseif (self::is_p2sh($currency, $addr[0])) {
                    $wver = 18;
                } else {
                    throw new \Exception(sprintf('Unknown address type for %s', $currency));
                }

                $wprog = substr($addr, 1, strlen($addr));
            }
            
            return self::tagged('f', bitstring.pack("uint:5", $wver) + $wprog);
        } else {
            throw new \Exception(sprintf('Support for currency %s not implemented', $currency));
        }
    }
    
    /**
     * Decode all supported fallback addresses.
     * 
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
        return $prefix == self::$base58_prefix_map[$currency][0];
    }
    
    /**
     * @param  string $currency
     * @param  string $prefix
     * @return bool
     */
    public static function is_p2sh(string $currency, string $prefix) : bool
    {
        return $prefix == self::$base58_prefix_map[$currency][1];
    }
    
    /**
     * "Tagged" field containing BitArray.
     * 
     * @param  string $char The delimiting character from a Lightning Payment
     *                      that denotes the "tagged part".
     * @param  array  $l
     * @return array Binary An array of byte-strings
     */
    public static function tagged(string $char, array $l) : array
    {
        // Tagged fields need to be zero-padded to 5 bits.
        while (sizeof($l) % 5 != 0) {
            array_push($l, '0b0');
        }
        
        // TODO (See unit tests) - how to zero-pad to 5 bits??
        // bitstring.pack("uint:5, uint:5, uint:5", CHARSET.find(char), (l.len / 5) / 32, (l.len / 5) % 32) + l
        $size = sizeof($l);
        return [
            pack('C*', strpos(self::$charset, $char), ($size / 5) / 32, ($size / 5) % 32),
            $l
        ];
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
