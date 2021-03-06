<?php

namespace IPLib\Test\Addresses;

use IPLib\Address\IPv4;
use IPLib\Address\IPv6;

class InvalidTest extends \PHPUnit_Framework_TestCase
{
    public function invalidAddressesProvider()
    {
        return array(
            array(''),
            array(0),
            array(null),
            array(false),
            array(array()),
            array('127'),
            array('127.0'),
            array('127.0.0'),
            array('127.0.0.0.0'),
            array('127.0.0.300'),
            array('127.0.00 .1'),
            array('127.0. 0.1'),
            array('127. '),
            array(':::1'),
            array('::1::'),
            array('1::1::1'),
            array('1.1.1.1/8'),
            array('1.-.1.1'),
            array('00000::1'),
            array('z::'),
        );
    }

    /**
     * @dataProvider invalidAddressesProvider
     */
    public function testInvalidAddresses($address)
    {
        $str = @strval($address);
        $this->assertNull(IPv4::fromString($address), "'$str' has been detected as a valid IPv4 address, but it shouldn't");
        $this->assertNull(IPv6::fromString($address), "'$str' has been detected as a valid IPv6 address, but it shouldn't");
    }
}
