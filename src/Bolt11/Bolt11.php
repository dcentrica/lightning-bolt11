<?php

/**
 * @author  Russell Michell for Dcentrica 2019 <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

namespace Dcentrica\Bolt11\Bolt11;

use BitWasp\Bech32;

/**
 * Takes userland payment data and encodes it to the BOLT-11 standard, suited for
 * display for example in QR codes used in Lightning Network payments.
 * 
 * @see https://github.com/bitcoinjs/bolt11
 * @see https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md
 * @see https://medium.com/lightwork/development-tooling-for-invoices-in-lightning-network-applications-cf3d194dc2bb
 * 
 * Example output can be seen here: https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md#examples
 */
class Bolt11
{

    /**
     * An array of default userland options for each field. These can be modified
     * by individual setters named after the applicable array key.
     * 
     * @var array
     */
    protected $opts = [
        'paymentAmount' => null,
        'network' => 'tb',
        'payeeNodeKey' => null,
        'recoveryFlag' => 0, // <-- TODO
        'txSignature' => null,
        'paymentHash' => null,
        'description' => null,
        'cltvExpiry' => null,
        'timestamp' => 0, // <-- TODO is this a tmestamp or an encoded timestamp?
    ];

    /**
     * The amount to request payment for.
     * 
     * Note: The spec suggests that payees can legitimately accept up to twice
     * the amount required for payment.
     * 
     * @param  float  $amount     Amount to pay in Bitcoin (0.xxx) BTC
     * @param  string $multiplier One of: m|u|n|p (milli|micro|nano|pico)
     * @return Dcentrica\Bolt11
     */
    public function setAmount(float $amount, string $multiplier): Dcentrica\Bolt11
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
    public function setNetwork(string $network): Dcentrica\Bolt11
    {
        $this->opts['network'] = "ln{$network}";

        return $this;
    }

    /**
     * @param  string $nodeKey The public key of the node payment is to be made to.
     * @return Dcentrica\Bolt11
     */
    public function setPayeeNodeKey(string $nodeKey): Dcentrica\Bolt11
    {
        $this->opts['payeeNodeKey'] = $nodeKey;

        return $this;
    }

    /**
     * @param  int $flag TODO
     * @return Dcentrica\Bolt11
     */
    public function setRecoveryFlag(int $flag): Dcentrica\Bolt11
    {
        $this->opts['recoveryFlag'] = $flag;

        return $this;
    }

    /**
     * @param  string $sig TODO
     * @return Dcentrica\Bolt11
     */
    public function setTxSignature(string $sig): Dcentrica\Bolt11
    {
        $this->opts['txSignature'] = $sig;

        return $this;
    }

    /**
     * @param  string $hash TODO
     * @return Dcentrica\Bolt11
     */
    public function setPaymentHash(string $hash): Dcentrica\Bolt11
    {
        $this->opts['paymentHash'] = $hash;

        return $this;
    }

    /**
     * @param  string $description TODO
     * @return Dcentrica\Bolt11
     */
    public function setDescription(string $description): Dcentrica\Bolt11
    {
        $this->opts['description'] = $description;

        return $this;
    }

    /**
     * @todo Should $expiry be an int timestamp or a stringy encoded timestamp?
     * @param  string $expiry TODO
     * @return Dcentrica\Bolt11
     */
    public function setCltvExpiry(string $expiry): Dcentrica\Bolt11
    {
        $this->opts['cltvExpiry'] = $expiry;

        return $this;
    }

    /**
     * Timestamp setter. Lightning timestamps are big-endian binary encoded, prior
     * to being set.
     * 
     * @todo Run acheck on $timestamp to see if it's already formatted, prevent reformatting it,
     * @param  int $timestamp
     * @return Dcentrica\Bolt11
     */
    public function setTimestamp(int $timestamp): Dcentrica\Bolt11
    {
        $this->opts['timestamp'] = $timestamp;

        return $this;
    }

    /**
     * @see    $this->getNetwork()
     * @param  string $paymentReq
     * @return Dcentrica\Bolt11
     */
    public function setPaymentRequest(string $paymentReq): Dcentrica\Bolt11
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
    public function getPrefix(): string
    {
        return "{$this->opts['network']}{$this->opts['amount']}";
    }

    /**
     * Return the Human-readable component of the complete payment string as a
     * 2-value array. This is is known internally as the "Human Readable Part".
     * 
     * @param  string $invoice
     * @return array           An array of: prefix,amount
     */
    public function getHumanPart(string $invoice = ''): string
    {
        $invoice ?: $this->getEncoded();
        $preImage = $invoice->preImage();
        $field = $preImage->getField('Human');

        return $field->value();
    }

    /**
     * Return the Machine-readable component of the complete payment string as a
     * 3-value array. This is is known internally as the "Data Part".
     * 
     * @param  string $invoice
     * @return array           An array of: timestamp,tagged-parts,signature
     */
    public function getDataPart(string $invoice = ''): string
    {
        $invoice ?: $this->getEncoded();
        $preImage = $invoice->preImage();
        $field = $preImage->getField('Data');

        return $field->value();
    }

    /**
     * Return the pre-image. The lengthy-as raw string that comprises a Lightning
     * invoice before it is bech32 encoded.
     * 
     * e.g., "lightning:lnbc1pvjluezpp5qqqsyqcyq5rqwzqfqqqsyqcyq5rqwzqfqqqsyqcyq5rqwzqfqypqdpl2pkx2ctnv5sxxmmwwd5kgetjypeh2ursdae8g6twvus8g6rfwvs8qun0dfjkxaq8rkx3yf5tcsyz3d73gafnh3cax9rn449d9p5uxz9ezhhypd0elx87sjle52x86fux2ypatgddc6k63n7erqz25le42c4u4ecky03ylcqca784w"
     * 
     * @return string
     */
    public function getPreimage() : string
    {
        return sprintf('%s%s1%d%s%s%s%s',
            $this->getScheme(),
            $this->getPrefix(),
            $this->opts['timestamp'] ?? time(),
            "pp5{$this->opts['paymentHash']}", // TODO see spec...
            "dp1{$this->opts['description']}", // TODO see spec...
            $this->opts['signature'],
            $this->getBech32Checksum()
        );
    }
    
    /**
     * Alias of getPreImage().
     * 
     * @return string
     */
    public function getRaw() : string
    {
        return $this->getPreimage();
    }
    
    /**
     * @todo
     * @return string
     */
    public function getBech32Checksum()
    {
        return ''; // TODO
    }
    
    /**
     * @param  string $hash The payment hash generated e.g. by a Lightning node.
     * @return $this
     */
    public function setPaymentHash(string $hash)
    {
        $this->opts['paymentHash'] = $hash;
        
        return $this;
    }

    /**
     * Generate an encoded Lightning Payment Invoice. Defaults to current bech32
     * encoding, which is standard across Bitcoin Segwit and Lightning Network at
     * time of writing.
     * 
     * @return string
     */
    public function encode(string $invoice = ''): string
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
    public function decode(string $invoice): string
    {
        return Bech32::decode($invoice);
    }

    /**
     * @return bool
     */
    public function isTxComplete(): bool
    {
        return false;
    }

    /**
     * Return a Field object model that represents the desired Lightning payment
     * field.
     * 
     * @param  string $field The name of the Lightning field to return.
     * @return Dcentrica\Bolt11\Field\Field
     * @throws Exception
     */
    public function getField(string $field) : Dcentrica\Bolt11\Field\Field
    {
        $class = sprintf('Dcentrica\Bolt11\Field\%s', ucfirst(strtolower($field)));

        if (!class_exists($class)) {
            throw new Exception('Iinvoice field not found.');
        }

        return new $class($this->getPreimage());
    }
    
    /**
     * @return string
     */
    public function getPrefix() : string
    {
        return "ln{$this->opts['network']}";  
    }
    
    /**
     * The prefix-prefix if you like.
     * 
     * @return string
     */
    public function getScheme() : string
    {
        return 'lightning:';
    }

    /**
     * Simple way to return any of the keyed options. Because some options may
     * legitimately be set to null, we instead return 'noop' to signal when a passed
     * option was not found.
     * 
     * @param  string $opt This should be the name of a known key in the $opts
     *                     array.
     * @return mixed
     * @throws Exception
     */
    public function __get($opt)
    {   
        if (array_key_exists($opt, $this->opts)) {
            return $this->opts[$name];
        }

        return 'noop';
    }

}
