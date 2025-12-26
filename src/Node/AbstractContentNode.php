<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;

abstract class AbstractContentNode extends AbstractNode {
    
    protected string $name;
    
    private int $position;
    
    protected FileContainer $ownerFile;
    
    protected int $contentOffset;
    
    abstract protected function loadContent(LeanElement $strucElement): void;
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $parentNode = $this->getParentNode();
        
        $this->ownerFile = $parentNode instanceof FileContainer ? $parentNode : $parentNode->getOwnerFile();
        
        $this->name = (string) $strucElement->getAttribute('name');
        $this->position = $strucElement->hasAttribute('position') ? (int) $this->ownerFile->evaluate($strucElement->getAttribute('position')) : 0;
        
        $this->contentOffset = $this->position;
        if ($parentNode instanceof AbstractContentNode) {
            $this->contentOffset += $parentNode->getContentOffset();
        }
    }
    
    protected function getOwnerFile(): FileContainer {
        return $this->ownerFile;
    }
    
    public function getOwnerSavegame(): SavegameNode {
        return $this->ownerFile->getOwnerSavegame();
    }
    
    protected function loadNode(LeanElement $strucElement): void {
        $this->loadContent($strucElement);
    }
    
    public function getContentOffset(): int {
        return $this->contentOffset;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function appendBuildChild(BuildableInterface $childNode): void {
        assert($childNode instanceof AbstractContentNode);
        
        parent::appendBuildChild($childNode);
    }
}