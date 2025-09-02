<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use PHPUnit\Framework\TestCase;

/**
 * NodeEvaluatorInterfaceTest
 *
 * @see NodeEvaluatorInterface
 *
 * @todo auto-generated
 */
class NodeEvaluatorInterfaceTest extends TestCase {

    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(NodeEvaluatorInterface::class), "Failed to load interface 'Slothsoft\Savegame\NodeEvaluatorInterface'!");
    }
}