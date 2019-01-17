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
 * Ported to PHP from: https://github.com/rustyrussell/lightning-payencode/blob/master/lightning-address.py
 * 
 * @see https://github.com/bitcoinjs/bolt11
 * @see https://github.com/rustyrussell/lightning-payencode and port that to PHP
 * @see https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md
 * @see https://medium.com/lightwork/development-tooling-for-invoices-in-lightning-network-applications-cf3d194dc2bb
 * 
 * Example output can be seen here: https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md#examples
 * 
 * The PayEncode class allows us to encode and decode lightning payment requests.
 */
class PayEncode
{
    /**
     * Encode.
     * 
     * @param  array $opts
     * @retrun string A Bech32 encoded string
     */
    public function encode(array $opts) : string
    {
        
    }
    
    /**
     * Decode.
     * 
     * @param  array $opts
     * @retrun string A raw, unencoded string
     */
    public function decode(array $opts) : string
    {
        
    }

}
