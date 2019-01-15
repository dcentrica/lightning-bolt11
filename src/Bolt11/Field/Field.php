<?php

/**
 * @author  Russell Michell for Dcentrica 2019 <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

namespace Dcentrica\Bolt11\Field;

/**
 * Base class with common functionality for each LN payment string's component
 * otherwise known as a "field" in the BOLT11 spec.
 */
abstract class Field
{
    /**
     * @var string
     */
    protected $preImage = '';
    
    /**
     * @param string $preimage
     */
    public function __construct(string $preimage)
    {
        $this->preImage = $preimage;
    }
    
    /**
     * @return string
     */
    public function value() : string;
    
}
