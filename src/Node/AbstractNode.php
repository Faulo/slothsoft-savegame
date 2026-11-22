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
    
    /**
     * Attach the node to its parent when applicable and load its structure, content, and children.
     */
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
    
    /**
     * Return the savegame root that owns this node.
     */
    abstract public function getOwnerSavegame(): SavegameNode;
    
    protected function getConverter(): Converter {
        return Converter::getInstance();
    }
    
    /**
     * Return the immediate parent, or null for a root node.
     */
    public function getParentNode(): ?AbstractNode {
        return $this->parentNode;
    }
    
    /**
     * Append a buildable child here or forward it to the parent if this node is omitted from the build.
     */
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
     * Return the ordered buildable children, or null when none have been appended.
     *
     * @return iterable<BuildableInterface>|null
     */
    public function getBuildChildren(): ?iterable {
        return $this->childNodeList;
    }
    
    /**
     * Return an empty cache key component by default.
     */
    public function getBuildHash(): string {
        return '';
    }
    
    /**
     * Yield the ancestors in order from the root to the immediate parent.
     *
     * @return iterable<AbstractNode>
     */
    public function getBuildAncestors(): iterable {
        if ($this->parentNode) {
            yield from $this->parentNode->getBuildAncestors();
            yield $this->parentNode;
        }
    }
}