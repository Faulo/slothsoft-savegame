<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use PHPUnit\Framework\TestCase;

/**
 * AbstractNodeTest
 *
 * @see AbstractNode
 *
 * @todo auto-generated
 */
class AbstractNodeTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractNode::class), "Failed to load class 'Slothsoft\Savegame\Node\AbstractNode'!");
    }
}