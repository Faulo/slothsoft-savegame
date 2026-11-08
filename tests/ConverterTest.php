<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame;

use PHPUnit\Framework\TestCase;
use RangeException;

/**
 * ConverterTest
 *
 * @see Converter
 *
 */
final class ConverterTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Converter::class), "Failed to load class 'Slothsoft\Savegame\Converter'!");
    }
    
    /**
     * @dataProvider integerFormats
     */
    public function testIntegerFormats(int $value, string $bigEndian): void {
        $converter = new Converter();
        $size = strlen($bigEndian);
        $this->assertSame($bigEndian, $converter->encodeInteger($value, $size));
        $this->assertSame($value, $converter->decodeInteger($bigEndian, $size));
        $this->assertSame(strrev($bigEndian), $converter->encodeInteger($value, $size, 'little'));
        $this->assertSame($value, $converter->decodeInteger(strrev($bigEndian), $size, 'little'));
        // Alternate byte order for the same bytes to exercise the decode cache.
        $expected = 0;
        foreach (str_split(strrev($bigEndian)) as $byte) {
            $expected = ($expected << 8) | ord($byte);
        }
        $this->assertSame($expected, $converter->decodeInteger($bigEndian, $size, 'little'));
        $this->assertSame($value, $converter->decodeInteger($bigEndian, $size));
    }
    
    /**
     * @return iterable<string, array{int, string}>
     */
    public function integerFormats(): iterable {
        yield 'one byte' => [0x7f, "\x7f"];
        yield 'two bytes' => [0x1234, "\x12\x34"];
        yield 'three bytes' => [999999, "\x0f\x42\x3f"];
        yield 'four bytes' => [0x12345678, "\x12\x34\x56\x78"];
        yield 'unsigned maximum' => [0xffffffff, "\xff\xff\xff\xff"];
    }
    
    public function testSignedLittleEndianConversion(): void {
        $converter = new Converter();
        $this->assertSame("\xfe\xff\xff", $converter->encodeSignedInteger(-2, 3, 'little'));
        $this->assertSame(-2, $converter->decodeSignedInteger("\xfe\xff\xff", 3, 'little'));
        $this->assertSame("\xff\xff\xfe", $converter->encodeSignedInteger(-2, 3));
        $this->assertSame(-2, $converter->decodeSignedInteger("\xff\xff\xfe", 3));
        foreach ([1, 2, 3, 4] as $size) {
            $minimum = -(1 << ($size * 8 - 1));
            $bigEndian = "\x80" . str_repeat("\0", $size - 1);
            $this->assertSame($minimum, $converter->decodeSignedInteger($bigEndian, $size));
            $this->assertSame($minimum, $converter->decodeSignedInteger(strrev($bigEndian), $size, 'little'));
        }
    }
    
    public function testRejectsUnknownByteOrder(): void {
        $this->expectException(RangeException::class);
        (new Converter())->decodeInteger("\0", 1, 'native');
    }
    
    public function testHexadecimalWhitespaceAndEmptyInput(): void {
        $converter = new Converter();
        $this->assertSame("\xAB\x01", $converter->encodeBinary(" AB\n01\t"));
        $this->assertSame('', $converter->encodeBinary(''));
    }
    
    /**
     * @dataProvider invalidHexadecimal
     */
    public function testRejectsInvalidHexadecimal(string $hex): void {
        $this->expectException(RangeException::class);
        (new Converter())->encodeBinary($hex);
    }
    
    /** @return iterable<string, array{string}> */
    public function invalidHexadecimal(): iterable {
        yield 'odd length' => ['ABC'];
        yield 'invalid digits' => ['GG'];
    }
    
    public function testRejectsTruncatedInteger(): void {
        $this->expectException(RangeException::class);
        (new Converter())->decodeInteger("\x01", 2);
    }
}
