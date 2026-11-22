<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use Ds\Vector;
use http\Exception\InvalidArgumentException;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Editor;

class SavegameNode extends AbstractNode implements BuildableInterface {
    
    /**
     * Return the XML tag name for a savegame.
     */
    public function getBuildTag(): string {
        return 'savegame';
    }
    
    /**
     * Combine the structure hash with a hash of the archive build keys.
     */
    public function getBuildHash(): string {
        if ($this->valueHash === null) {
            $hash = '';
            foreach ($this->getArchiveNodes() as $node) {
                $hash .= $node->getBuildHash();
            }
            $this->valueHash = md5($hash);
        }
        return $this->fileHash . '-' . $this->valueHash;
    }
    
    /**
     * Invalidate the cached hash derived from the savegame's archives.
     */
    public function setDirty(): void {
        $this->valueHash = null;
    }
    
    /**
     * Return the schema version, save ID, and structure hash as XML attributes.
     */
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
    
    /** @var array<string, Vector<LeanElement>> */
    private array $globalElements;
    
    private int $valueIdCounter = 0;
    
    /**
     * Associate the savegame root with its editor and node factory.
     */
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
    
    /**
     * Set the identifier emitted in the savegame XML.
     */
    public function setSaveId(string $saveId): void {
        $this->saveId = $saveId;
    }
    
    /**
     * Return the editor that owns the savegame tree.
     */
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
            $this->globalElements[$element->getAttribute('global-id')] = new Vector($element->getChildren());
        }
        
        foreach ($archiveList as $element) {
            $this->loadChild($element);
        }
    }
    
    protected function loadNode(LeanElement $strucElement): void {
    }
    
    /**
     * Append an archive node to the savegame's build children.
     */
    public function appendBuildChild(BuildableInterface $node): void {
        assert($node instanceof ArchiveNode);
        
        parent::appendBuildChild($node);
    }
    
    /**
     * Yield the savegame's archive nodes in build order.
     *
     * @return ArchiveNode[]
     */
    public function getArchiveNodes(): iterable {
        foreach ($this->getBuildChildren() ?? [] as $node) {
            if (! $node instanceof ArchiveNode) {
                throw new \LogicException('Unexpected child node type.');
            }
            yield $node;
        }
    }
    
    /**
     * Find an archive by its relative path in the savegame structure.
     */
    public function getArchiveByPath(string $path): ArchiveNode {
        if ($nodeList = $this->getBuildChildren()) {
            /** @var ArchiveNode $node */
            foreach ($nodeList as $node) {
                if ($node->getArchivePath() === $path) {
                    return $node;
                }
            }
        }
        throw new InvalidArgumentException("Not an archive: $path");
    }
    
    /**
     * Return the reusable structure elements registered under the given global ID, or null if absent.
     *
     * @return Vector<LeanElement>|null
     */
    public function getGlobalElementsById(string $id): ?Vector {
        return $this->globalElements[$id] ?? null;
    }
    
    /**
     * Allocate the next sequential value ID for this savegame.
     */
    public function nextValueId(): int {
        return ++$this->valueIdCounter;
    }
    
    /**
     * Index the loaded file values across all archives by savegame value ID.
     *
     * @return array<int, AbstractValueContent>
     */
    public function getValueMap(): array {
        $ret = [];
        if ($archiveList = $this->getBuildChildren()) {
            /** @var ArchiveNode $archive */
            foreach ($archiveList as $archive) {
                if ($fileList = $archive->getBuildChildren()) {
                    /** @var FileContainer $file */
                    foreach ($fileList as $file) {
                        $valueList = $file->getValueList();
                        foreach ($valueList as $value) {
                            $ret[$value->getValueId()] = $value;
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    /**
     * Create and initialize a node under the supplied parent using this savegame's factory.
     */
    public function createNode(LeanElement $strucElement, AbstractNode $parentValue): AbstractNode {
        return $this->factory->createNode($strucElement, $parentValue);
    }
    
    /**
     * Return this root node as the owning savegame.
     */
    public function getOwnerSavegame(): SavegameNode {
        return $this;
    }
}
