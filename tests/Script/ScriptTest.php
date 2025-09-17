<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Script;

use PHPUnit\Framework\TestCase;

/**
 * ScriptTest
 *
 * @see Script
 *
 * @todo auto-generated
 */
class ScriptTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Script::class), "Failed to load class 'Slothsoft\Savegame\Script\Script'!");
    }
}