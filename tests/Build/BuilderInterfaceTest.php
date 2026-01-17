<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Build;

use PHPUnit\Framework\TestCase;

/**
 * BuilderInterfaceTest
 *
 * @see BuilderInterface
 *
 * @todo auto-generated
 */
final class BuilderInterfaceTest extends TestCase {
    
    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(BuilderInterface::class), "Failed to load interface 'Slothsoft\Savegame\Build\BuilderInterface'!");
    }
}