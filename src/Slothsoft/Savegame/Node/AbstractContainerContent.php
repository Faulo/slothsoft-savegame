<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuildableInterface;
declare(ticks = 1000);

abstract class AbstractContainerContent extends AbstractContentNode implements BuildableInterface
{

    protected function loadContent(EditorElement $strucElement)
    {}
}
