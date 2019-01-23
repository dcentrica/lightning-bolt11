<?php

/**
 * @author  Russell Michell for Dcentrica 2019 <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

namespace Dcentrica\Bolt11;

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;

/**
 * This is a lightweight wrapper around functionality found in the
 * BitcoinPHP\BitcoinECDSA\BitcoinECDSA library.
 */
class Base58
{
    /**
     * Simple Bitcoin-specific Base58 encode.
     * 
     * @param  string $data
     * @param  string $endian
     * @return string
     * @throws Exception
     */
    public function encode(string $data, string $endian) : string
    {        
        $littleEndianness = self::endianness($endian);
        
        return (new BitcoinECDSA())->base58_encode($data, $littleEndianness);
    }
    
    /**
     * Simple Bitcoin-specific Base58 decode.
     * 
     * @param  string $encodedData
     * @param  string $endian
     * @return string (Hex)
     */
    public function decode(string $encodedData, string $endian) : string
    {
        $littleEndianness = self::endianness($endian);
        
        return (new BitcoinECDSA())->base58_decode($encodedData, $littleEndianness);
    }
    
    /**
     * Simple Bitcoin-specific Base58 decode, with address format verification
     * aka "check decoding".
     * 
     * @param  string    $encodedData
     * @param  string    $endian
     * @return string    A base58, hex-encoded string.
     * @throws Exception
     */
    public function checkDecode(string $encodedData, string $endian) : string
    {
        $decoded = $this->decode($encodedData, $endian);
        
        if (!(new BitcoinECDSA())->validateAddress($decoded)) {
            throw new \Exception('Invalid checksum in address!');
        }
        
        return $decoded;
    }
    
    /**
     * Allows users to express endianness as "big" or "little".
     * 
     * @param  string $endian
     * @return bool
     */
    private static function endianness(string $endian) : bool
    {
        if (!in_array($endian, ['big', 'little'], true)) {
            throw new \Exception('Endianness can only b expressed as "big" or "little"');
        }
        
        return ($endian === 'little' ? true : false);
    }
    
}
