<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use PHPUnit\Framework\TestCase;

/**
 * NodeFactoryTest
 *
 * @see NodeFactory
 *
 * @todo auto-generated
 */
final class NodeFactoryTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(NodeFactory::class), "Failed to load class 'Slothsoft\Savegame\Node\NodeFactory'!");
    }
}