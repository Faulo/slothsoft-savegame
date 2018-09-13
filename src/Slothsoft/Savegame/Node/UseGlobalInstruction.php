<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;

class UseGlobalInstruction extends AbstractContentNode
{

    private $globalRef;

    protected function loadStruc(LeanElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->globalRef = (string) $strucElement->getAttribute('ref');
    }

    protected function loadContent(LeanElement $strucElement)
    {}

    protected function loadChildren(LeanElement $strucElement)
    {
        if ($instructionList = $this->getOwnerSavegame()->getGlobalElementsById($this->globalRef)) {
            foreach ($instructionList as $instruction) {
                $this->loadChild($instruction);
            }
        }
    }
}

