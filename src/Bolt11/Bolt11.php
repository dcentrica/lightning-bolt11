<?php

/**
 * @author  Russell Michell for Dcentrica 2019 <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

namespace Dcentrica\Bolt11\Bolt11;

use BitWasp\Bech32;

/**
 * A class that will take userland payment data and encode it to the Bolt-11 standard
 * suited for display in QR codes used in Bitcoin Lightning Payments.
 * 
 * @see https://github.com/bitcoinjs/bolt11
 * @see https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md
 * @see https://medium.com/lightwork/development-tooling-for-invoices-in-lightning-network-applications-cf3d194dc2bb
 */
class Bolt11
{
    /**
     * An array of default userland options. These can be modified by individual
     * named after the applicable array key.
     * 
     * @var array
     */
    protected $opts = [
        'paymentAmount' => 0,
        'network' => 'tb',
        'payeeNodeKey' => null,
        'paymentRequest' => null,
        'recoveryFlag' => 0, // <-- TODO
        'txSignature' => null,
        'paymentHash' => null,
        'description' => null,
        'cltvExpiry' => null,
        'timestamp' => 0,
    ];
    
    /**
     * @param  float  $amount     Amount to pay in Milli-Bitcoin (0.xxx) BTC
     * @param  string $multiplier One of: m|u|n|p (milli|micro|nano|pico)
     * @return Dcentrica\Bolt11
     */
    public function setAmount(float $amount, string $multiplier) : Dcentrica\Bolt11
    {
        $this->opts['paymentAmount'] = "{$amount}{$multiplier}";
        
        return $this;
    }
    
    /**
     * All valid payment requests begin with "lnxx" prefix where:
     * 
     * - bc = BitCoin (mainnet) OR
     * - tb = TestnetBitcoin OR
     * - bcrt = BitCoin RegTest
     * 
     * @param string $network One of: bc|tb|bcrt
     * @return Dcentrica\Bolt11
     */
    public function setNetwork(string $network) : Dcentrica\Bolt11
    {
        $this->opts['network'] = "ln{$network}";
        
        return $this;
    }
    
    /**
     * @param  string $nodeKey The public key of the node payment is to be made to.
     * @return Dcentrica\Bolt11
     */
    public function setPayeeNodeKey(string $nodeKey) : Dcentrica\Bolt11
    {
        $this->opts['payeeNodeKey'] = $nodeKey;
        
        return $this;
    }
    
    /**
     * @param  int $flag TODO
     * @return Dcentrica\Bolt11
     */
    public function setRecoveryFlag(int $flag) : Dcentrica\Bolt11
    {
        $this->opts['recoveryFlag'] = $flag;
        
        return $this;
    }
    
    /**
     * @param  string $sig TODO
     * @return Dcentrica\Bolt11
     */
    public function setTxSignature(string $sig) : Dcentrica\Bolt11
    {
        $this->opts['txSignature'] = $sig;
        
        return $this;
    }
    
    /**
     * @param  string $hash TODO
     * @return Dcentrica\Bolt11
     */
    public function setPaymentHash(string $hash) : Dcentrica\Bolt11
    {
        $this->opts['paymentHash'] = $hash;
        
        return $this;
    }
    
    /**
     * @param  string $description TODO
     * @return Dcentrica\Bolt11
     */
    public function setDescription(string $description) : Dcentrica\Bolt11
    {
        $this->opts['description'] = $description;
        
        return $this;
    }
    
    /**
     * @param  string $expiry TODO
     * @return Dcentrica\Bolt11
     */
    public function setCltvExpiry(string $expiry) : Dcentrica\Bolt11
    {
        $this->opts['cltvExpiry'] = $expiry;
        
        return $this;
    }
    
    /**
     * @param  int $timestamp
     * @return Dcentrica\Bolt11
     */
    public function setTimestamp(int $timestamp) : Dcentrica\Bolt11
    {
        $this->opts['timestamp'] = $timestamp;
        
        return $this;
    }
    
    /**
     * @see    $this->getNetwork()
     * @param  string $paymentReq
     * @return Dcentrica\Bolt11
     */
    public function setPaymentRequest(string $paymentReq) : Dcentrica\Bolt11
    {
        // See: https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md#the-same-on-testnet-with-a-fallback-address-mk2qpyatskicvfvutaqlbryyccrxmuaghp
        $network = $this->opts['network'];
        $amount = "{$this->opts['amount']}m";
        $bech32sep = 1;
        $timestamp = null; // TODO
        $_paymentHash = null;
        $paymentHash = "p{$_paymentHash}"; // TODO
        $_taggedField = null; // TODO
        $taggedField = "h$_taggedField";
        
        $this->opts['paymentRequest'] = "{$this->getPrefix()}1{$paymentReq}";
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPrefix() : string
    {
        return "{$this->opts['network']}{$this->opts['amount']}";
    }
    
    /**
     * Return the Human-readable component of the complete payment string.  This
     * is known internally as the "Human-Readable Part".
     * 
     * @param  string $invoice
     * @return string
     */
    public function getHuman(string $invoice = '') : string
    {
        $invoice ?: $this->getEncoded();
        
        return $this->decode($invoice)[0];
    }
    
    /**
     * Return the Machine-readable component of the complete payment string. This
     * is known internally as the "Data Part".
     * 
     * @param  string $invoice
     * @return string
     */
    public function getData(string $invoice = '') : string
    {
        $invoice ?: $this->getEncoded();
        
        return $this->decode($invoice)[1];
    }
    
    /**
     * Return the pre-image. The lengthy-as string that comprises a Lightning invoice
     * before it is bech32 encoded.
     * 
     * @param  int   $type 1 is to return data as an array, 0 returns as a string.
     * @return mixed array|string
     */
    public function getPreimage(int $type = 1)
    {
        return $type === 1 ? [] : '';
    }
    
    /**
     * Generate an encoded Lightning Payment Invoice. Defaults to current bech32
     * encoding, which is standard across Bitcoin Segwit and Lightning Network at
     * time of writing.
     * 
     * @return string
     */
    public function encode(string $invoice = '') : string
    {
        return Bech32::encode($this->getHuman(), $this->preImage(1));
    }
    
    /**
     * Returns a decoded equivalent of the given Bech32 encoded $invoice as a 2-value
     * array:
     * 
     * 0 => Human Readable Part
     * 1 => Data Part
     * 
     * @param  string $invoice
     * @return array
     */
    public function decode(string $invoice) : string
    {
        return Bech32::decode($invoice);
    }
    
    /**
     * @return bool
     */
    public function isTxComplete() : bool
    {
        return false;
    }
    
    /**
     * Return the desired Lightning payment field.
     * 
     * @param  string $field The name of the Lightning field to return.
     * @return Dcentrica\Bolt11\Field\Field
     * @throws Exception
     */
    public function getField(string $field) : string
    {
        $class = sprintf('Dcentrica\Bolt11\Field\%s', ucfirst(strtolower($field)));
        
        if (!class_exists($class)) {
            throw new Exception('Lightning invoice component not found');
        }
        
        return new $class($this->getPreimage());
    }
    
}

