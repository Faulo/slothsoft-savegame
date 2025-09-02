<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Script;

use PHPUnit\Framework\TestCase;

/**
 * ParserTest
 *
 * @see Parser
 *
 * @todo auto-generated
 */
class ParserTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(Parser::class), "Failed to load class 'Slothsoft\Savegame\Script\Parser'!");
    }
}