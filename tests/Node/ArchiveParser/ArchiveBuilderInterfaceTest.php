<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node\ArchiveParser;

use PHPUnit\Framework\TestCase;

/**
 * ArchiveBuilderInterfaceTest
 *
 * @see ArchiveBuilderInterface
 *
 * @todo auto-generated
 */
class ArchiveBuilderInterfaceTest extends TestCase {
    
    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(ArchiveBuilderInterface::class), "Failed to load interface 'Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface'!");
    }
}