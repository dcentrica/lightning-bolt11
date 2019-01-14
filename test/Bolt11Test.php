<?php

/**
 * @author  Russell Michell 2019 for Decentrica <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

use Dcentrica\LightningNetwork\Bolt11;
use PHPUnit_Framework_TestCase;

/**
 * TODO: See https://github.com/Bit-Wasp/bech32
 */
class Bolt11Test extends PHPUnit_Framework_TestCase
{    
    /**
     * Does the payment pre-image contain the mandatory "p" field?
     */
    public function testContainsPField()
    {
		$encoded = $this->dummyAddress();
        $preImage = $encoded->getPreimage();
        $field = $encoded->getField('p')->value();
        
        $this->assertContains($field, $preImage);
    }
    
    /**
     * Does the pre-image contain the mandatory "payment_hash" field?
     */
    public function testContainsPaymentRequestField()
    {
		$encoded = $this->dummyAddress();
        $preImage = $encoded->getPreimage();
        $field = $encoded->getField('paymentRequest')->value();
        
        $this->assertContains(hash('sha256', $field), $preImage);
    }
    
    /**
     * Does the pre-image contain a mandatory "d" or "h" field?
     * 
     * - "d" is for "Data"
     * - "d" is for ""
     */
    public function testContainsDorHFields()
    {
		$encoded = $this->dummyAddress();
        $preImage = $encoded->getPreimage();
        $dField = $encoded->getField('d')->value();
        $hField = $encoded->getField('h')->value();
        
        if ($dField) {
            $this->assertIsUtf8($dField);
            $this->assertGreaterThan(9  , mb_strlen($dField));
        }
        
        if ($hField) {
            $this->assertGreaterThan(9  , mb_strlen($hField));
        }
        
        // Pseudo-assertion!!
        $this->assertContainsOneOf([$dField, $hField], $preImage);
    }
    
    /**
     * Does the pre-image contain a mandatory "cltv_expiry" field, if exists
     */
    public function testContainsCltvExpiryField()
    {
		$encoded = $this->dummyAddress();
        $preImage = $encoded->getPreimage();
        $field = $encoded->getField('CltvExpiry');
        
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
		$encoded = $this->dummyAddress();
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
		$encoded = $this->dummyAddress();
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
		$encoded = $this->dummyAddressNotHasPublicChannel();
        $preImage = $encoded->getPreimage();
        $fields = $encoded->getField('r')->values();
        
        $this->assertGreaterThanOrEqual(1, $fields);
        
		$encoded = $this->dummyAddressHasPublicChannel();
        $preImage = $encoded->getPreimage();
        $fields = $encoded->getField('r')->values();
        
        $this->assertEquals(0, count($fields));        
    }
    
    /**
     * 
     * @return Dcentrica\LightningNetwork\Bolt11
     */
    private function dummyAddress()
    {
		return (new Bolt11())
			->setPayeeNode()
			->setTheOther()
			->Foo();
    }

}

