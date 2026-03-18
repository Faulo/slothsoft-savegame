<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;

final class ForEachFileNode extends AbstractNode {
    
    private string $list;
    
    private string $rangeStart;
    
    private string $rangeEnd;
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        $this->list = (string) $strucElement->getAttribute('list');
        $this->rangeStart = (string) $strucElement->getAttribute('range-start');
        $this->rangeEnd = (string) $strucElement->getAttribute('range-end');
    }
    
    protected function loadNode(LeanElement $strucElement): void {}
    
    public function loadChildren(LeanElement $strucElement): void {
        foreach ($this->getFileNames() as $name) {
            $strucData = [];
            $strucData['file-name'] = $name;
            
            $childElement = LeanElement::createOneFromArray(NodeFactory::TAG_FILE, $strucData, $strucElement->getChildren());
            
            $this->loadChild($childElement);
        }
    }
    
    public function getOwnerSavegame(): SavegameNode {
        return $this->getOwnerArchive()->getOwnerSavegame();
    }
    
    private function getOwnerArchive(): ArchiveNode {
        return $this->getParentNode();
    }
    
    private function getFileNames(): iterable {
        $names = $this->getOwnerArchive()->getFileNames();
        
        if (strlen($this->list)) {
            $range = preg_split('~\s+~', $this->list, 0, PREG_SPLIT_NO_EMPTY);
            $names = $names->filter(function (string $name) use ($range): bool {
                return in_array($name, $range);
            });
        }
        
        if (strlen($this->rangeStart)) {
            $range = $this->rangeStart;
            $names = $names->filter(function (string $name) use ($range): bool {
                return strcmp($name, $range) >= 0;
            });
        }
        
        if (strlen($this->rangeEnd)) {
            $range = $this->rangeEnd;
            $names = $names->filter(function (string $name) use ($range): bool {
                return strcmp($name, $range) <= 0;
            });
        }
        
        return $names;
    }
}