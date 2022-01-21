<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;

abstract class AbstractContentNode extends AbstractNode {

    private $name;

    private $position;

    protected $ownerFile;

    protected $contentOffset;

    abstract protected function loadContent(LeanElement $strucElement);

    public function getBuildAttributes(BuilderInterface $builder): array {
        return [
            'name' => $this->name
        ];
    }

    protected function loadStruc(LeanElement $strucElement) {
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

    /**
     *
     * @return \Slothsoft\Savegame\Node\FileContainer
     */
    protected function getOwnerFile(): FileContainer {
        return $this->ownerFile;
    }

    /**
     *
     * @return \Slothsoft\Savegame\Node\SavegameNode
     */
    public function getOwnerSavegame(): SavegameNode {
        return $this->ownerFile->getOwnerSavegame();
    }

    protected function loadNode(LeanElement $strucElement) {
        $this->loadContent($strucElement);
    }

    public function getContentOffset() {
        return $this->contentOffset;
    }

    /**
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function appendBuildChild(BuildableInterface $childNode) {
        assert($childNode instanceof AbstractContentNode);

        parent::appendBuildChild($childNode);
    }
}