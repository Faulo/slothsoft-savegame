<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use RangeException;

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
        
        switch ($strucElement->getAttribute('position-from', 'parent')) {
            case 'root':
                break;
            case 'parent':
                if ($parentNode instanceof AbstractContentNode) {
                    $this->contentOffset += $parentNode->getContentOffset();
                }
                break;
            case 'sibling':
                /** @var AbstractValueContent $previousSibling */
                $previousSibling = null;
                foreach ($this->getOwnerFile()->getValueList() as $node) {
                    if ($node === $this) {
                        break;
                    }
                    if ($node instanceof AbstractValueContent) {
                        $previousSibling = $node;
                    }
                }
                
                if ($previousSibling) {
                    $this->contentOffset += $previousSibling->getContentOffset() + $previousSibling->getContentSize();
                }
                break;
        }
        
        if ($strucElement->hasAttribute('position-at-string')) {
            $position = $this->ownerFile->findStringAtOrAfter($strucElement->getAttribute('position-at-string'), $this->contentOffset);
            if ($position === null) {
                $search = $strucElement->getAttribute('position-at-string');
                throw new RangeException("Failed to find string '$search' at or after position $this->contentOffset in " . $this->ownerFile->getFileName());
            }
            $this->position = $position - $this->contentOffset;
            $this->contentOffset = $position;
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