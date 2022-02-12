<?php

namespace IO\Data;

use PHPUnit\Framework\TestCase;
use TorneLIB\IO\Data\Strings;

require_once(__DIR__ . '/../vendor/autoload.php');

/**
 * Class stringsTest
 * @package IO\Data
 * @version 6.1.0
 * @since 6.0
 */
class stringsTest extends TestCase
{
    /**
     * @test
     * Test conversion of snake_case to camelCase.
     */
    public function getCamelCase()
    {
        static::assertTrue(
            (new Strings())->getCamelCase('base64url_encode') === 'base64urlEncode'
        );
    }

    /**
     * @test
     * Test conversion of camelCase to snake_case.
     */
    public function getSnakeCase()
    {
        static::assertTrue(
            (new Strings())->getSnakeCase('base64urlEncode') === 'base64url_encode'
        );
    }

    /**
     * @test
     * Translate stringed by to integerbytes.
     */
    public function getBytes()
    {
        static::assertTrue(
            (int)(new Strings())->getBytes('2048M') === 2147483648
        );
    }

    /**
     * @test
     * Testing base64 encoded string with the standardize camelCase.
     */
    public function getBase64Camel()
    {
        static::assertTrue(
            (new Strings())->base64urlEncode('base64') === 'YmFzZTY0'
        );
    }

    /**
     * @test
     * Testing the old snakecase variant.
     */
    public function getBase64Snake()
    {
        static::assertTrue(
            (new Strings())->base64url_encode('base64') === 'YmFzZTY0'
        );
    }

    /**
     * @test
     * @since 6.1.6
     */
    public function obfuscateRandomly()
    {
        $obfuscated = (new Strings())->getObfuscatedString('A long obfuscated string');
        static::assertTrue((bool)preg_match('/\*/', $obfuscated));
    }

    /**
     * @test
     * @since 6.1.6
     */
    public function obfuscateFull()
    {
        $strings = new Strings();
        $obfuscateFirst = $strings->getObfuscatedStringFull('Just a string.', 2);
        $obfuscateSecond = $strings->getObfuscatedStringFull('Just a string.', 3);
        $obfuscateThird = $strings->getObfuscatedStringFull('Just a string.', 4, 0);
        // Breaking rules.
        $obfuscateFourth = $strings->getObfuscatedStringFull('Just', 5, 5);
        static::assertTrue(
            $obfuscateFirst === 'Ju************.' &&
            $obfuscateSecond === 'Jus************.' &&
            $obfuscateThird === 'Just************' &&
            $obfuscateFourth === 'Just'
        );
    }

    /**
     * @test
     * @since 6.1.6
     */
    public function obfuscateFullRaisedLimit()
    {
        $obfuscated = (new Strings())->getObfuscatedStringFull('Just a string.', 2);
        static::assertTrue($obfuscated === 'J************.');
    }

    /**
     * @test
     * @testdox Avoiding exceptions.
     * @since 6.1.6
     */
    public function obfuscateTooShort()
    {
        $obfuscated = (new Strings())->getObfuscatedStringFull('AB');
        static::assertTrue($obfuscated === 'AB');
    }
}
