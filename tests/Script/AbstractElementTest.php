<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Script;

use PHPUnit\Framework\TestCase;

/**
 * AbstractElementTest
 *
 * @see AbstractElement
 *
 * @todo auto-generated
 */
final class AbstractElementTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractElement::class), "Failed to load class 'Slothsoft\Savegame\Script\AbstractElement'!");
    }
}