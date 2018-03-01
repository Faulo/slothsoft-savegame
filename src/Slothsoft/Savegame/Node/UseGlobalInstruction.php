<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class UseGlobalInstruction extends AbstractContentNode
{

    private $globalRef;

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->globalRef = (string) $strucElement->getAttribute('ref');
    }

    protected function loadContent(EditorElement $strucElement)
    {}

    protected function loadChildren(EditorElement $strucElement)
    {
        if ($instructionList = $this->getOwnerSavegame()->getGlobalElementsById($this->globalRef)) {
            foreach ($instructionList as $instruction) {
                $this->loadChild($instruction);
            }
        }
    }
}

