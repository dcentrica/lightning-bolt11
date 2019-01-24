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
    
    // Fixtures as constants
    const RHASH = '0001020304050607080900010203040506070809000102030405060708090102';       // Unhex
    const CONVERSION_RATE = 1200;
    const PRIVKEY = 'e126f68f7eafcc8b74f54d269fe206be715000f94dac067d1c04a8ca3b2db734';     // To bin
    const PUBKEY = '03e7156ae33b0a208d0744199163177e909e80176e55d97a2f221ede0f934dd9ad';    // To bin
    
    /**
     * @var array
     */
    protected $fixture = [];
    
    public function __construct()
    {
        // Modify the fixtures
        $this->fixture['RHASH'] = hex2bin(self::RHASH);
        $this->fixture['CONVERSION_RATE'] = self::CONVERSION_RATE;
        $this->fixture['PRIVKEY'] = pack('H*', self::PRIVKEY);
        $this->fixture['PUBKEY'] = pack('H*', self::PUBKEY);
    }
    
    // Tests: LNAddr::shorten_amount()
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

    public function test_roundtrip()
    {
        $longdescription = ''
            . 'One piece of chocolate cake, one icecream cone, one'
            . ' pickle, one slice of swiss cheese, one slice of salami,'
            . ' one lollypop, one piece of cherry pie, one sausage, one'
            . ' cupcake, and one slice of watermelon';
    
        // new LNAddr(string $paymentHash, string $amount, string $currency, array $tags);
        $tests = [
            (new LNAddr(
                $this->fixture['RHASH'],
                '',
                '',
                $tags = ['d', '']
            )),
            (new LNAddr(
                $this->fixture['RHASH'],
                $amount = new Decimal('0.001'),
                '',
                $tags = [
                    ['d', '1 cup coffee'],
                    ['x', 60],
                ]
            )),
            (new LNAddr(
                $this->fixture['RHASH'],
                $amount = new Decimal('1'),
                '',
                $tags = [
                    ['h', $longdescription],
                ]
            )),
            (new LNAddr(
                $this->fixture['RHASH'],
                '',
                $currency = 'tb',
                $tags = [
                    ['f', 'mk2QpYatsKicvFVuTAQLBryyccRXMUaGHP'],
                    ['h', $longdescription],
                ]
            )),
            (new LNAddr(
                $this->fixture['RHASH'],
                $amount = 24,
                '',
                $tags = [
                    ['r', 
                        [
                            [
                                hex2bin('029e03a901b85534ff1e92c43c74431f7ce72046060fcf7a95c37e148f78c77255'),
                                hex2bin('0102030405060708'),
                                1,
                                20,
                                3
                            ],
                            [
                                hex2bin('039e03a901b85534ff1e92c43c74431f7ce72046060fcf7a95c37e148f78c77255'),
                                hex2bin('030405060708090a'),
                                2,
                                30,
                                4
                            ]
                        ]
                    ],
                    ['f', '1RustyRX2oai4EYYDpQGWvEL62BBGqN9T'],
                    ['h', $longdescription]
                ]
            )),
            (new LNAddr(
                $this->fixtures['RHASH'],
                $amount = 24,
                '',
                $tags = [
                    ['f', '3EktnHQD7RiAE6uzMj2ZifT9YgRrkSgzQX'],
                    ['h', $longdescription]
                ]
            )),
            (new LNAddr(
                $this->fixture['RHASH'],
                $amount = 24,
                '',
                $tags = [
                    ['f', 'bc1qw508d6qejxtdg4y5r3zarvary0c5xw7kv8f3t4'],
                    ['h', $longdescription]
                ]
            )),
            (new LNAddr(
                $this->fixture['RHASH'],
                $amount = 24,
                '',
                $tags = [
                    ['f', 'bc1qrp33g0q5c5txsp9arysrx4k6zdkfs4nce4xj0gdcccefvpysxf3qccfmv3'],
                    ['h', $longdescription]
                ]
            )),
            (new LNAddr(
                $this->fixture['RHASH'],
                $amount = 24,
                '',
                $tags = [
                    ['n', hex2bin($this->fixture['PUBKEY'])],
                    ['h', $longdescription]
                ]
            )),
        ];
        
        // Roundtrip
        foreach ($tests as $t) {
            $o = lndecode(lnencode($t, $this->fixture['PRIVKEY']));
            $this->compare($t, $o);
        }
    }
    
    public function testTagged()
    {
        // "0b010010000000010" is yielded from python3 64bit:
        // >>> import bitstring
        // >>> print(bitstring.pack("uint:5, uint:5, uint:5", 1, (67 / 5) / 32, (67 / 5) % 32) + '0x00010203040506070809000102030405060708090001020304050607080901020')
        // <BitStream> 0x081a00020406080a0c0e101200020406080a0c0e101200020406080a0c0e10120204, 0b000
        $this->assertEquals(
            ['0x081a00020406080a0c0e101200020406080a0c0e101200020406080a0c0e10120204', '0b000'],
            LNAddr::tagged('p', '0x00010203040506070809000102030405060708090001020304050607080901020')
        );
    }

}
