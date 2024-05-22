<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;

class ForEachFileNode extends AbstractNode {

    private $fileRange;

    protected function loadStruc(LeanElement $strucElement) {
        parent::loadStruc($strucElement);
        $this->fileRange = (string) $strucElement->getAttribute('file-range');
    }

    protected function loadNode(LeanElement $strucElement) {}

    public function loadChildren(LeanElement $strucElement) {
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
        if (strlen($this->fileRange)) {
            $range = preg_split('~\s+~', $this->fileRange, 0, PREG_SPLIT_NO_EMPTY);
            $names = array_filter($names, function ($name) use ($range) {
                return in_array($name, $range);
            });
        }
        return $names;
    }
}