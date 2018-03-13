<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Build\BuildableInterface;


abstract class AbstractContentNode extends AbstractNode
{

    private $name;

    private $position;

    protected $ownerFile;

    protected $contentOffset;

    abstract protected function loadContent(EditorElement $strucElement);

    public function getBuildAttributes(BuilderInterface $builder): array
    {
        return [
            'name' => $this->name
        ];
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $parentNode = $this->getParentNode();
        
        $this->ownerFile = $parentNode instanceof FileContainer ? $parentNode : $parentNode->getOwnerFile();
        
        $this->name = (string) $strucElement->getAttribute('name');
        $this->position = (int) $strucElement->getAttribute('position', 0, $this->ownerFile);
        
        $this->contentOffset = $this->position;
        if ($parentNode instanceof AbstractContentNode) {
            $this->contentOffset += $parentNode->getContentOffset();
        }
    }

    /**
     *
     * @return \Slothsoft\Savegame\Node\FileContainer
     */
    public function getOwnerFile()
    {
        return $this->ownerFile;
    }

    /**
     *
     * @return \Slothsoft\Savegame\Editor
     */
    public function getOwnerEditor(): Editor
    {
        return $this->ownerFile->getOwnerEditor();
    }

    /**
     *
     * @return \Slothsoft\Savegame\Node\SavegameNode
     */
    public function getOwnerSavegame(): SavegameNode
    {
        return $this->ownerFile->getOwnerSavegame();
    }

    protected function loadNode(EditorElement $strucElement)
    {
        $this->loadContent($strucElement);
    }

    public function getContentOffset()
    {
        return $this->contentOffset;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function appendBuildChild(BuildableInterface $childNode)
    {
        assert($childNode instanceof AbstractContentNode);
        
        parent::appendBuildChild($childNode);
    }
}