<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Build\XmlBuilder;

class SavegameNode extends AbstractNode implements BuildableInterface {

    public function getBuildTag(): string {
        return 'savegame.editor';
    }

    public function getBuildHash(): string {
        return $this->fileHash;
    }

    public function getBuildAttributes(BuilderInterface $builder): array {
        return [
            'xmlns' => 'http://schema.slothsoft.net/savegame/editor',
            'version' => '0.3',
            'save-id' => $builder->escapeAttribute($this->saveId),
            'file-hash' => $this->fileHash
        ];
    }

    /**
     *
     * @var \Slothsoft\Savegame\Editor
     */
    private $ownerEditor;

    private $factory;

    private $globalElements;

    private $saveId;

    private $fileHash;

    private $valueIdCounter = 0;

    public function __construct(Editor $ownerEditor, NodeFactory $factory) {
        $this->ownerEditor = $ownerEditor;
        $this->factory = $factory;
    }

    protected function loadStruc(LeanElement $strucElement) {
        parent::loadStruc($strucElement);

        $this->saveId = (string) $strucElement->getAttribute('save-id');
        $this->fileHash = (string) $strucElement->getAttribute('file-hash');

        $this->globalElements = [];
    }

    public function getOwnerEditor(): Editor {
        return $this->ownerEditor;
    }

    protected function loadChildren(LeanElement $strucElement) {
        $archiveList = [];
        $globalList = [];

        foreach ($strucElement->getChildren() as $element) {
            switch ($element->getTag()) {
                case NodeFactory::TAG_ARCHIVE:
                    $archiveList[] = $element;
                    break;
                case NodeFactory::TAG_GLOBAL:
                    $globalList[] = $element;
                    break;
                case NodeFactory::TAG_GLOBALS:
                    $this->loadChildren($element);
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

    protected function loadNode(LeanElement $strucElement) {}

    public function appendBuildChild(BuildableInterface $node) {
        assert($node instanceof ArchiveNode);

        parent::appendBuildChild($node);
    }

    public function getArchiveNodes(): iterable {
        return $this->getBuildChildren() ?? [];
    }

    public function getArchiveById(string $id): ArchiveNode {
        if ($nodeList = $this->getBuildChildren()) {
            foreach ($nodeList as $node) {
                if ($node->getArchiveId() === $id) {
                    return $node;
                }
            }
        }
    }

    public function getGlobalElementsById(string $id) {
        return $this->globalElements[$id] ?? null;
    }

    public function nextValueId(): int {
        return ++ $this->valueIdCounter;
    }

    public function getValueMap(): array {
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

    public function createNode(LeanElement $strucElement, AbstractNode $parentValue): AbstractNode {
        return $this->factory->createNode($strucElement, $parentValue);
    }

    public function getOwnerSavegame(): SavegameNode {
        return $this;
    }

    public function getChunkWriter(): ChunkWriterInterface {
        $builder = new XmlBuilder($this);
        $builder->setCacheDirectory(sys_get_temp_dir());
        return $builder;
    }
}