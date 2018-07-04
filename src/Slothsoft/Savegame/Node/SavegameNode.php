<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Build\XmlBuilder;

class SavegameNode extends AbstractNode implements BuildableInterface
{

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
        
        foreach ($archiveList as $element) {
            $this->loadChild($element);
        }
    }

    protected function loadNode(EditorElement $strucElement)
    {}

    public function appendBuildChild(BuildableInterface $node)
    {
        assert($node instanceof ArchiveNode);
        
        parent::appendBuildChild($node);
    }
    
    public function getArchiveNodes(): iterable
    {
        return $this->getBuildChildren() ?? [];
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
    
    
    
//     public function toFileName(): string
//     {
//         return 'savegame.xml';
//     }
//     public function toFile(): SplFileInfo
//     {
//         $builder = new XmlBuilder();
//         $handle = $builder->buildStream($this);
//         $file = FileInfoFactory::createFromResource($handle);
//         fclose($handle);
//         return $file;
//     }
//     public function toString(): string
//     {
//         $builder = new XmlBuilder($this);
//         $builder->registerAttributeBlacklist([
//             'position',
//             'bit',
//             'encoding'
//         ]);
//         $xml = '';
//         foreach ($builder->toChunks() as $data) {
//             $xml .= $data;
//         }
//         return $xml;
//         //return $builder->buildString($this);
//     }
    
    public function createNode(EditorElement $strucElement, AbstractNode $parentValue) : AbstractNode
    {
        return $this->factory->createNode($strucElement, $parentValue);
    }
    public function getOwnerSavegame(): SavegameNode {
        return $this;
    }
    
    public function getChunkWriter() : ChunkWriterInterface {
        $builder = new XmlBuilder($this);
        $builder->registerAttributeBlacklist([
            'position',
            'bit',
            'encoding'
        ]);
        return $builder;
    }
}