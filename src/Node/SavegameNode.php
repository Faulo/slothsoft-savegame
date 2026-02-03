<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Ds\Vector;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;

class SavegameNode extends AbstractNode implements BuildableInterface {
    
    public function getBuildTag(): string {
        return 'savegame';
    }
    
    public function getBuildHash(): string {
        if ($this->valueHash === null) {
            $hash = '';
            /** @var ArchiveNode $node */
            foreach ($this->getArchiveNodes() as $node) {
                $hash .= $node->getBuildHash();
            }
            $this->valueHash = md5($hash);
        }
        return $this->fileHash . '-' . $this->valueHash;
    }
    
    public function setDirty(): void {
        $this->valueHash = null;
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return [
            'version' => '0.4',
            'save-id' => $builder->escapeAttribute($this->saveId),
            'file-hash' => $this->fileHash
        ];
    }
    
    private Editor $ownerEditor;
    
    private NodeFactory $factory;
    
    private string $saveId;
    
    private string $fileHash;
    
    private ?string $valueHash = null;
    
    private array $globalElements;
    
    private int $valueIdCounter = 0;
    
    public function __construct(Editor $ownerEditor, NodeFactory $factory) {
        $this->ownerEditor = $ownerEditor;
        $this->factory = $factory;
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->saveId = (string) $strucElement->getAttribute('save-id');
        $this->fileHash = (string) $strucElement->getAttribute('file-hash');
        
        $this->globalElements = [];
    }
    
    public function setSaveId(string $saveId): void {
        $this->saveId = $saveId;
    }
    
    public function getOwnerEditor(): Editor {
        return $this->ownerEditor;
    }
    
    protected function loadChildren(LeanElement $strucElement): void {
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
    
    protected function loadNode(LeanElement $strucElement): void {}
    
    public function appendBuildChild(BuildableInterface $node): void {
        assert($node instanceof ArchiveNode);
        
        parent::appendBuildChild($node);
    }
    
    /**
     *
     * @return ArchiveNode[]
     */
    public function getArchiveNodes(): iterable {
        return $this->getBuildChildren() ?? [];
    }
    
    public function getArchiveByPath(string $path): ArchiveNode {
        if ($nodeList = $this->getBuildChildren()) {
            /** @var ArchiveNode $node */
            foreach ($nodeList as $node) {
                if ($node->getArchivePath() === $path) {
                    return $node;
                }
            }
        }
    }
    
    public function getGlobalElementsById(string $id): ?Vector {
        return $this->globalElements[$id] ?? null;
    }
    
    public function nextValueId(): int {
        return ++ $this->valueIdCounter;
    }
    
    public function getValueMap(): array {
        $ret = [];
        if ($archiveList = $this->getBuildChildren()) {
            /** @var ArchiveNode $archive */
            foreach ($archiveList as $archive) {
                if ($fileList = $archive->getBuildChildren()) {
                    /** @var FileContainer $file */
                    foreach ($fileList as $file) {
                        if ($valueList = $file->getValueList()) {
                            /** @var AbstractValueContent $value */
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
}