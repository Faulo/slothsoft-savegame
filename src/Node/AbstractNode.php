<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use Ds\Vector;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Converter;

abstract class AbstractNode {
    
    abstract protected function loadNode(LeanElement $strucElement): void;
    
    private ?AbstractNode $parentNode = null;
    
    /** @var Vector<BuildableInterface>|null */
    private ?Vector $childNodeList = null;
    
    public function init(LeanElement $strucElement, ?AbstractNode $parentNode = null): void {
        $this->parentNode = $parentNode;
        
        if ($this->parentNode and $this instanceof BuildableInterface) {
            $this->parentNode->appendBuildChild($this);
        }
        
        $this->loadStruc($strucElement);
        $this->loadNode($strucElement);
        $this->loadChildren($strucElement);
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
    }
    
    protected function loadChildren(LeanElement $strucElement): void {
        foreach ($strucElement->getChildren() as $strucElement) {
            $this->loadChild($strucElement);
        }
    }
    
    final protected function loadChild(LeanElement $strucElement): void {
        $this->getOwnerSavegame()->createNode($strucElement, $this);
    }
    
    abstract public function getOwnerSavegame(): SavegameNode;
    
    protected function getConverter(): Converter {
        return Converter::getInstance();
    }
    
    public function getParentNode(): ?AbstractNode {
        return $this->parentNode;
    }
    
    public function appendBuildChild(BuildableInterface $childNode): void {
        if ($this instanceof BuildableInterface) {
            if ($this->childNodeList === null) {
                $this->childNodeList = new Vector();
            }
            $this->childNodeList[] = $childNode;
        } else {
            if ($this->parentNode === null) {
                throw new \LogicException('A non-buildable node must have a parent.');
            }
            $this->parentNode->appendBuildChild($childNode);
        }
    }
    
    /**
     * @return iterable<BuildableInterface>|null
     */
    public function getBuildChildren(): ?iterable {
        return $this->childNodeList;
    }
    
    public function getBuildHash(): string {
        return '';
    }
    
    /**
     * @return iterable<AbstractNode>
     */
    public function getBuildAncestors(): iterable {
        if ($this->parentNode) {
            yield from $this->parentNode->getBuildAncestors();
            yield $this->parentNode;
        }
    }
}