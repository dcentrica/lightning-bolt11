<?php

/**
 * @author  Russell Michell 2019 for Decentrica <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */
use Dcentrica\Bolt11\Bolt11;
use PHPUnit_Framework_TestCase;

/**
 * TODO: See https://github.com/Bit-Wasp/bech32
 * 
 * Example output for encode()
 */
class Bolt11Test extends PHPUnit_Framework_TestCase
{

    /**
     * Is the encoded payment request bech32 encoded?
     * @todo Should the _whole thing_ be bech32, or just the payment_request section?
     */
    public function testIsBech32()
    {
        $encoded = $this->dummyPayment();
        $field = $encoded->getField('paymentRequest')->value();

        // TODO startsWith!
        // TODO WHat else is unqiue to a bech32 encoded payment?
        $this->assertStartsWith('bc1', $en); // <-- Bitcoin only!
    }

    /**
     * Does the payment pre-image contain the mandatory "p" field?
     */
    public function testContainsPField()
    {
        $encoded = $this->dummyPayment();
        $preImage = $encoded->getPreimage();
        $field = $encoded->getField('p')->value();

        $this->assertContains($field, $preImage);
    }

    /**
     * Does the pre-image contain the mandatory "payment_hash" field?
     */
    public function testContainsPaymentRequestField()
    {
        $encoded = $this->dummyPayment();
        $preImage = $encoded->getPreimage();
        $field = $encoded->getField('paymentRequest')->value();

        $this->assertContains(hash('sha256', $field), $preImage);
    }

    /**
     * Does the pre-image contain a mandatory "d" or "h" field?
     * 
     * - "d" is for "Data"
     * - "h" is for "Human"
     */
    public function testContainsDorHField()
    {
        $encoded = $this->dummyPayment();
        $preImage = $encoded->getPreimage();
        $dField = $encoded->getField('d')->value();
        $hField = $encoded->getField('h')->value();

        if ($dField) {
            $this->assertIsUtf8($dField);
            $this->assertGreaterThan(9, mb_strlen($dField));
        }

        if ($hField) {
            $this->assertGreaterThan(9, mb_strlen($hField));
        }

        // Pseudo-assertion!!
        $this->assertContainsOneOf([$dField, $hField], $preImage);
    }

    /**
     * Does the pre-image contain a mandatory "cltv_expiry" field, if exists
     */
    public function testContainsCltvExpiryField()
    {
        $encoded = $this->dummyPayment();
        $preImage = $encoded->getPreimage();
        $field = $encoded->getField('CltvExpiry')->value();

        if (strstr($preImage, $field)) {
            // TODO
            $this->assertTrue(false);
        }
    }

    /**
     * Does the pre-image contain a mandatory "n" field, if exists?
     */
    public function testContainsValidNField()
    {
        $encoded = $this->dummyPayment();
        $preImage = $encoded->getPreimage();
        $field = $encoded->getField('n')->value();

        if (strstr($preImage, $field)) {
            // TODO
            $this->assertTrue(false);
        }
    }

    /**
     * Does the pre-image contain a mandatory "f" field, if exists?
     */
    public function testContainsValidFField()
    {
        $encoded = $this->dummyPayment();
        $preImage = $encoded->getPreimage();
        $field = $encoded->getField('f')->value();

        if (strstr($preImage, $field)) {
            // TODO
            $this->assertTrue(false);
        }
    }

    /**
     * Does the pre-image contain a mandatory "r" field, if exists?
     */
    public function testContainsRField()
    {
        $encoded = $this->dummyPaymentHasRField();
        $preImage = $encoded->getPreimage();
        $fields = $encoded->getField('r')->value();

        $this->assertGreaterThanOrEqual(1, count($fields));

        $encoded = $this->dummyPaymentHasPublicChannel();
        $preImage = $encoded->getPreimage();
        $fields = $encoded->getField('r')->value();

        $this->assertEquals(0, count($fields));
    }
    
    /**
     * Is the Human Readable Part valid?
     */
    public function testGetHumanPartIsValid()
    {
        $encoded = $this->dummyPayment();
        
        $this->assertArrayKeysExist(['prefix', 'amount'], $encoded->getHumanPart());
        $this->assertNotStartsWith('0', $encoded->getHuman()['amount']);
        $this->assertNotStartsWith('-', $encoded->getHuman()['amount']);
    }
    
    /**
     * Is the Human Readable Part valid?
     */
    public function testGetDataPartIsValid()
    {
        $encoded = $this->dummyPayment();
        $preImage = $encoded->getPreimage();
        
        $this->assertArrayKeysExist(['timestamp', 'tags', 'signature'], $encoded->getDataPart());
        $this->assertIsBigEndian($encoded->getHuman()['timestamp']);
        $this->assertIsBigEndian($encoded->getHuman()['timestamp']);
    }

    /**
     * 
     * @return Dcentrica\LightningNetwork\Bolt11
     * @todo   Set Cltv correctly
     * @todo   Set correct timestamp
     * @todo   Set recovery flag properly
     * @todo   Set Tx Signature properly. Figure out what should be encoded and do the encoding in the setter itself
     * @todo   Set PaymentRequest properly. Figure out what should be passed
     * @todo   Set PaymentHash properly. Figure out what should be passed
     */
    private function dummyPayment()
    {
        return (new Bolt11())
            ->setPaymentAmount(0.1, 'm')
            ->setNetwork('tb')
            ->setPayeeNodeKey('00102030405060708090001020304050607080900010203040506070809010')
            ->setRecoveryFlag(0)
            ->setTxSignature('Bar')
            ->setPaymentHash('2ba5c0920f71b0e910c2e9fad1adf7269723a1c330d881003a2347a624844984')
            ->setDescription('Paid in full')
            ->setCltvExpiry(12345678);
    }

}
