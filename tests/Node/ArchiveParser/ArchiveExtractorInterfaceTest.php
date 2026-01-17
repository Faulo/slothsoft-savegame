<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node\ArchiveParser;

use PHPUnit\Framework\TestCase;

/**
 * ArchiveExtractorInterfaceTest
 *
 * @see ArchiveExtractorInterface
 *
 * @todo auto-generated
 */
final class ArchiveExtractorInterfaceTest extends TestCase {
    
    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(ArchiveExtractorInterface::class), "Failed to load interface 'Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface'!");
    }
}