<?php

declare(strict_types=1);

/**
 * @author  Russell Michell 2019 for Decentrica <russ@theruss.com>
 * @package Dcentrica\Bolt11
 */

namespace Dcentrica\Bolt11\Test;

use Dcentrica\Bolt11\LNAddr;
use PHPUnit\Framework\TestCase;

/**
 * TODO: See https://github.com/Bit-Wasp/bech32
 * 
 * Example output for encode()
 */
class Bolt11Test extends TestCase
{
    /**
     * 
     */
    public function test_shorten_amount()
    {
        $tests = [
            pow(10 / 10, 12) => '10p',
            pow(1000 / 10, 12) => '1n',
            pow(1200 / 10, 12) => '1200p',
            pow(123 / 10, 6) => '123u',
            (123 / 1000) => '123m',
            3 => '3',
        ];

        foreach ($tests as $i => $o) {
            $shortened = LNAddr::shorten_amount($i);
            $this->assertEquals($o, $shortened);
         //   $this->assertEquals($i, LNAddr::unshorten_amount($shortened));
        }
    }

}
