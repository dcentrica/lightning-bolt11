<?php

/**
 * @author  Russell Michell for Dcentrica 2019 <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

namespace Dcentrica\Bolt11\Field;

/**
 * Base class with common functionality for each LN payment string's component
 * otherwise known as a "field".
 */
abstract class Field
{
    /**
     * @return string
     */
    public function value() : string;
    
}
