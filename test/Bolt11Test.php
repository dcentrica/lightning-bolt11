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
 */
class Bolt11Test extends TestCase
{
    public function test_shorten_amount()
    {   
        $tests = [
            '10p'   =>  ((new Decimal(10))->toInt() / 10 ** 12),    // Python3: 1E-11, PHP7.2: 1.0E-11
            '1n'    =>  ((new Decimal(1000))->toInt() / 10 ** 12),  // Python3: 0.000123, PHP7.2:
            '1200p' =>  ((new Decimal(1200))->toInt() / 10 ** 12),  // Python3: 1.2E-9
            '123u'  =>  ((new Decimal(123))->toInt() / 10 ** 6),    // Python3: 1.2E-9
            '123m'  =>  ((new Decimal(123))->toInt() / 1000),
            '3'     =>  (new Decimal(3))->toInt(),
        ];

        foreach ($tests as $o => $i) {
            $input = new Decimal((string) $i);
            
            $this->assertEquals($o, LNAddr::shorten_amount($input));
            $this->assertEquals($input->toInt(), LNAddr::unshorten_amount(LNAddr::shorten_amount($input)));
        }
    }

}
