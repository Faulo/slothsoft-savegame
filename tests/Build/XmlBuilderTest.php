<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Build;

use PHPUnit\Framework\TestCase;

/**
 * XmlBuilderTest
 *
 * @see XmlBuilder
 *
 * @todo auto-generated
 */
class XmlBuilderTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(XmlBuilder::class), "Failed to load class 'Slothsoft\Savegame\Build\XmlBuilder'!");
    }
}