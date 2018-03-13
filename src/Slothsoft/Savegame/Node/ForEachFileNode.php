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
        $archive = $this->getParentNode();
        
        foreach ($archive->getFileNameList() as $name) {
            $strucData = [];
            $strucData['file-name'] = $name;
            
            $childElement = new EditorElement(EditorElement::NODE_TYPES['file'], $strucData, $strucElement->getChildren());
            
            $this->loadChild($childElement);
        }
    }
}