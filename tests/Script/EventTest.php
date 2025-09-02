<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Script;

use PHPUnit\Framework\TestCase;

/**
 * EventTest
 *
 * @see Event
 *
 * @todo auto-generated
 */
class EventTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(Event::class), "Failed to load class 'Slothsoft\Savegame\Script\Event'!");
    }
}