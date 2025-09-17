<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use PHPUnit\Framework\TestCase;

/**
 * EditorTest
 *
 * @see Editor
 *
 * @todo auto-generated
 */
class EditorTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Editor::class), "Failed to load class 'Slothsoft\Savegame\Editor'!");
    }
}