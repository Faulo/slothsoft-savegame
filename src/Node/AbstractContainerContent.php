<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;

abstract class AbstractContainerContent extends AbstractContentNode implements BuildableInterface
{

    protected function loadContent(LeanElement $strucElement)
    {}
}
