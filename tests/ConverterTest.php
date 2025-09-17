<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use PHPUnit\Framework\TestCase;

/**
 * ConverterTest
 *
 * @see Converter
 *
 * @todo auto-generated
 */
class ConverterTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Converter::class), "Failed to load class 'Slothsoft\Savegame\Converter'!");
    }
}