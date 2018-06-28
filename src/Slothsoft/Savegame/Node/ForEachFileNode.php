<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;

class ForEachFileNode extends AbstractNode
{

    protected function loadNode(EditorElement $strucElement)
    {}

    public function loadChildren(EditorElement $strucElement)
    {
        $archive = $this->getOwnerArchive();
        
        foreach ($archive->getFileNames() as $name) {
            $strucData = [];
            $strucData['file-name'] = $name;
            
            $childElement = new EditorElement(EditorElement::NODE_TYPES['file'], $strucData, $strucElement->getChildren());
            
            $this->loadChild($childElement);
        }
    }
    public function getOwnerSavegame(): SavegameNode
    {
        return $this->getOwnerArchive()->getOwnerSavegame();
    }
    private function getOwnerArchive() : ArchiveNode {
        return $this->getParentNode();
    }

}