<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\IO\AdapterTraits\DOMWriterFromStringTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Build\XmlBuilder;
use SplFileInfo;

class SavegameNode extends AbstractNode implements BuildableInterface, FileWriterInterface, DOMWriterInterface
{
    use DOMWriterFromStringTrait;

    /**
     *
     * @var \Slothsoft\Savegame\Editor
     */
    private $ownerEditor;
    
    private $factory;

    private $globalElements;

    private $saveId;

    private $valueIdCounter = 0;

    public function __construct(Editor $ownerEditor, NodeFactory $factory)
    {
        $this->ownerEditor = $ownerEditor;
        $this->factory = $factory;
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->saveId = (string) $strucElement->getAttribute('save-id');
        
        $this->globalElements = [];
    }

    public function getOwnerEditor(): Editor
    {
        return $this->ownerEditor;
    }

    public function loadChildren(EditorElement $strucElement)
    {
        log_execution_time(__FILE__, __LINE__);
        
        $archiveList = [];
        $globalList = [];
        
        foreach ($strucElement->getChildren() as $element) {
            switch ($element->getType()) {
                case EditorElement::NODE_TYPES['archive']:
                    $archiveList[] = $element;
                    break;
                case EditorElement::NODE_TYPES['global']:
                    $globalList[] = $element;
                    break;
            }
        }
        
        foreach ($globalList as $element) {
            $this->globalElements[$element->getAttribute('global-id')] = $element->getChildren();
        }
        
        log_execution_time(__FILE__, __LINE__);
        
        foreach ($archiveList as $element) {
            $this->loadChild($element);
        }
        
        log_execution_time(__FILE__, __LINE__);
    }

    protected function loadNode(EditorElement $strucElement)
    {}

    public function appendBuildChild(BuildableInterface $node)
    {
        assert($node instanceof ArchiveNode);
        
        parent::appendBuildChild($node);
    }

    public function getArchiveById(string $id): ArchiveNode
    {
        if ($nodeList = $this->getBuildChildren()) {
            foreach ($nodeList as $node) {
                if ($node->getArchiveId() === $id) {
                    return $node;
                }
            }
        }
    }

    public function getBuildTag(): string
    {
        return 'savegame.editor';
    }

    public function getBuildAttributes(BuilderInterface $builder): array
    {
        return [
            'xmlns' => 'http://schema.slothsoft.net/savegame/editor',
            'version' => '0.3',
            'save-id' => $builder->escapeAttribute($this->saveId)
        ];
    }

    public function getGlobalElementsById(string $id)
    {
        return $this->globalElements[$id] ?? null;
    }

    public function nextValueId(): int
    {
        return ++ $this->valueIdCounter;
    }

    public function getValueMap(): array
    {
        $ret = [];
        if ($archiveList = $this->getBuildChildren()) {
            foreach ($archiveList as $archive) {
                if ($fileList = $archive->getBuildChildren()) {
                    foreach ($fileList as $file) {
                        if ($valueList = $file->getValueList()) {
                            foreach ($valueList as $value) {
                                $ret[$value->getValueId()] = $value;
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    
    
    public function toFile(): SplFileInfo
    {
        $builder = new XmlBuilder();
        $handle = $builder->buildStream($this);
        $file = FileInfoFactory::createFromResource($handle);
        fclose($handle);
        return $file;
    }

    public function toString(): string
    {
        $builder = new XmlBuilder();
        $builder->registerAttributeBlacklist([
            'position',
            'bit',
            'encoding'
        ]);
        return $builder->buildString($this);
    }
    
    public function createNode(EditorElement $strucElement, AbstractNode $parentValue) : AbstractNode
    {
        return $this->factory->createNode($strucElement, $parentValue);
    }
    public function getOwnerSavegame(): SavegameNode {
        return $this;
    }


    
}