<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Build;

use PHPUnit\Framework\TestCase;

/**
 * BuildableInterfaceTest
 *
 * @see BuildableInterface
 *
 * @todo auto-generated
 */
final class BuildableInterfaceTest extends TestCase {
    
    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(BuildableInterface::class), "Failed to load interface 'Slothsoft\Savegame\Build\BuildableInterface'!");
    }
}