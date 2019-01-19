<?php

//declare(strict_types=1);

/**
 * @author  Russell Michell 2019 for Decentrica <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

namespace Dcentrica\Bolt11\Test;

use Dcentrica\Bolt11\LNAddr;
use PHPUnit\Framework\TestCase;
use Decimal\Decimal;

/**
 * @see https://github.com/rustyrussell/lightning-payencode/blob/master/test_lnpay.py
 * 
 * Example output for encode()
 */
class Bolt11Test extends TestCase
{
    /**
     * N.b. casting calculations to strings in PHP: PHP represents strings as
     * binary internally. All the more easy for chewing with...
     */
    public function test_shorten_amount()
    {   
        $tests = [
            '10p'   =>  (new Decimal(10))->div(10)->pow(12),    // 1E-11
            '1n'    =>  (new Decimal(1000))->div(10)->pow(12),  // 0.000123
            '1200p' =>  (new Decimal(1200))->div(10)->pow(12),  // 1.2E-9
            '123u'  =>  (new Decimal(123))->div(10)->pow(6),    // 1.2E-9
            '123m'  =>  (new Decimal(123))->div(1000)->toString(),
            '3'     =>  (new Decimal(3))->toString(),
        ];

        foreach ($tests as $o => $i) {
            $shortened = LNAddr::shorten_amount($i);
            $this->assertEquals($o, $shortened);
         //   $this->assertEquals($i, LNAddr::unshorten_amount($shortened));
        }
    }

}
