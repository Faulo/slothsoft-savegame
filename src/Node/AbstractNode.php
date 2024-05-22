<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Ds\Vector;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Converter;
use Slothsoft\Savegame\Build\BuildableInterface;

abstract class AbstractNode {

    abstract protected function loadNode(LeanElement $strucElement);

    /**
     *
     * @var \Slothsoft\Savegame\Node\AbstractNode
     */
    private $parentNode;

    private $childNodeList;

    public function init(LeanElement $strucElement, AbstractNode $parentNode = null) {
        $this->parentNode = $parentNode;

        if ($this->parentNode and $this instanceof BuildableInterface) {
            $this->parentNode->appendBuildChild($this);
        }

        $this->loadStruc($strucElement);
        $this->loadNode($strucElement);
        $this->loadChildren($strucElement);
    }

    public function load(): void {}

    protected function loadStruc(LeanElement $strucElement) {}

    protected function loadChildren(LeanElement $strucElement) {
        foreach ($strucElement->getChildren() as $strucElement) {
            $this->loadChild($strucElement);
        }
    }

    final protected function loadChild(LeanElement $strucElement) {
        $this->getOwnerSavegame()->createNode($strucElement, $this);
    }

    abstract public function getOwnerSavegame(): SavegameNode;

    /**
     *
     * @return \Slothsoft\Savegame\Converter
     */
    protected function getConverter() {
        return Converter::getInstance();
    }

    public function getParentNode() {
        return $this->parentNode;
    }

    public function appendBuildChild(BuildableInterface $childNode) {
        if ($this instanceof BuildableInterface) {
            if ($this->childNodeList === null) {
                $this->childNodeList = new Vector();
            }
            $this->childNodeList[] = $childNode;
        } else {
            $this->parentNode->appendBuildChild($childNode);
        }
    }

    public function getBuildChildren(): ?iterable {
        return $this->childNodeList;
    }

    public function getBuildHash(): string {
        return '';
    }

    public function getBuildAncestors(): iterable {
        if ($this->parentNode) {
            yield from $this->parentNode->getBuildAncestors();
            yield $this->parentNode;
        }
    }
}