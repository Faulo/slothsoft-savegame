<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use DomainException;
use Ds\Map;
use Ds\Set;
use Generator;
use LogicException;
use RuntimeException;
use Slothsoft\Core\Calendar\DateTimeFormatter;
use Slothsoft\Core\FileSystem;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\IO\Readable\FileReaderInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use SplFileInfo;

final class ArchiveNode extends AbstractNode implements BuildableInterface, FileWriterInterface, StringWriterInterface, FileReaderInterface {
    
    const NAMESPACE_SEPARATOR = '\\';
    
    private string $path;
    
    private string $name;
    
    private string $type;
    
    private string $timestamp;
    
    private ?string $fileHash = null;
    
    private int $size;
    
    private SplFileInfo $file;
    
    private LeanElement $strucElement;
    
    private SplFileInfo $extractDirectory;
    
    /** @var Map<string, SplFileInfo>|null */
    private ?Map $extractedFiles = null;
    
    /**
     * Return the XML tag name for an archive.
     */
    public function getBuildTag(): string {
        return 'archive';
    }
    
    /**
     * Return the archive cache key, rebuilding it from the file nodes when invalidated.
     */
    public function getBuildHash(): string {
        if ($this->fileHash === null) {
            $hash = '';
            foreach ($this->getFileNodes() as $node) {
                $hash .= $node->getBuildHash();
            }
            $this->fileHash = md5($hash);
        }
        return $this->fileHash;
    }
    
    /**
     * Invalidate this archive's cached build hash and the savegame hash that depends on it.
     */
    public function setDirty(): void {
        $this->fileHash = null;
        $this->getOwnerSavegame()->setDirty();
    }
    
    /**
     * Return the archive name, type, path, byte size, and timestamp as XML attributes.
     */
    public function getBuildAttributes(BuilderInterface $builder): array {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'path' => $this->path,
            'size' => $this->size,
            'timestamp' => $this->timestamp
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->path = (string) $strucElement->getAttribute('path');
        $this->name = (string) $strucElement->getAttribute('name', basename($this->path));
        $this->type = (string) $strucElement->getAttribute('type');
        
        $this->strucElement = $strucElement;
        
        $this->fromFile($this->getOwnerEditor()
            ->findGameFile($this->path));
    }
    
    protected function loadNode(LeanElement $strucElement): void {
    }
    
    protected function loadChildren(LeanElement $strucElement): void {
    }
    
    /**
     * Index extracted files and create their nodes on first load, optionally loading their contents.
     */
    public function load(bool $loadFiles = false): void {
        if ($this->extractedFiles === null) {
            $this->extractedFiles = new Map($this->indexArchiveFiles());
            parent::loadChildren($this->strucElement);
            
            if ($loadFiles) {
                foreach ($this->getFileNodes() as $fileNode) {
                    $fileNode->load();
                }
            }
        }
    }
    
    /** @return Generator<string, SplFileInfo> */
    private function indexArchiveFiles(): Generator {
        $directory = (string) $this->extractDirectory;
        $files = is_dir($directory) ? FileSystem::scanDir($directory, FileSystem::SCANDIR_FILEINFO) : [];
        if ($files === []) {
            $this->extractArchive();
            $files = FileSystem::scanDir($directory, FileSystem::SCANDIR_FILEINFO);
        }
        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo) {
                throw new LogicException('Expected archive scan to return file information.');
            }
            yield $file->getFilename() => $file;
        }
    }
    
    private function extractArchive(): void {
        $this->getArchiveExtractor()->extractArchive($this->file, $this->extractDirectory);
    }
    
    /**
     * Return the relative archive path from the savegame structure.
     */
    public function getArchivePath(): string {
        return $this->path;
    }
    
    /**
     * Return the configured archive name, which defaults to the path's basename.
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * Yield the file nodes defined for this archive in build order.
     *
     * @return FileContainer[]
     */
    public function getFileNodes(): iterable {
        foreach ($this->getBuildChildren() ?? [] as $node) {
            if (! $node instanceof FileContainer) {
                throw new LogicException('Unexpected child node type.');
            }
            yield $node;
        }
    }
    
    /**
     * Return the names indexed from the extracted archive after it has been loaded.
     *
     * @return Set<string>
     */
    public function getFileNames(): Set {
        if ($this->extractedFiles === null) {
            throw new LogicException('Archive has not been loaded.');
        }
        return $this->extractedFiles->keys();
    }
    
    /**
     * Look up the extracted file on disk by its exact name.
     */
    public function getFileByName(string $name): SplFileInfo {
        if (! isset($this->extractedFiles[$name])) {
            throw new DomainException(sprintf('Unknown file "%s"! Currently available from archive "%s": [%s]', $name, $this->file, implode(', ', [
                ...$this->getFileNames()
            ])));
        }
        
        return $this->extractedFiles[$name];
    }
    
    /**
     * Look up a file node in this archive by its exact name.
     */
    public function getFileNodeByName(string $name): FileContainer {
        $names = [];
        
        if ($nodeList = $this->getFileNodes()) {
            foreach ($nodeList as $node) {
                if ($node->getFileName() === $name) {
                    return $node;
                } else {
                    $names[] = $node->getFileName();
                }
            }
        }
        
        throw new DomainException(sprintf('Unknown file node "%s"! Currently available from archive "%s": [%s]', $name, $this->file, implode(', ', $names)));
    }
    
    /**
     * Append a file node to this archive's build children.
     */
    public function appendBuildChild(BuildableInterface $childNode): void {
        assert($childNode instanceof FileContainer);
        
        parent::appendBuildChild($childNode);
    }
    
    private function getArchiveBuilder(): ArchiveBuilderInterface {
        return $this->getOwnerEditor()->getArchiveBuilder($this->type);
    }
    
    private function getArchiveExtractor(): ArchiveExtractorInterface {
        return $this->getOwnerEditor()->getArchiveExtractor($this->type);
    }
    
    /**
     * Return the savegame root that contains this archive.
     */
    public function getOwnerSavegame(): SavegameNode {
        $parentNode = $this->getParentNode();
        assert($parentNode instanceof SavegameNode);
        return $parentNode;
    }
    
    private function getOwnerEditor(): Editor {
        return $this->getOwnerSavegame()->getOwnerEditor();
    }
    
    /**
     * Return the archive's current source file on disk.
     */
    public function toFile(): SplFileInfo {
        return $this->file;
    }
    
    /**
     * Return the configured archive name for use as a filename.
     */
    public function toFileName(): string {
        return $this->name;
    }
    
    /**
     * Build archive bytes from the file nodes, or return an empty string when there are none.
     */
    public function toString(): string {
        if ($childList = $this->getBuildChildren()) {
            return $this->getArchiveBuilder()->buildArchive($this->getFileNodes());
        } else {
            return '';
        }
    }
    
    /**
     * Write the supplied bytes to the archive's current source file and refresh its metadata.
     */
    public function fromString(string $sourceString): void {
        file_put_contents((string) $this->file, $sourceString);
        $this->fromFile($this->file);
    }
    
    /**
     * Set the archive source file, refresh its metadata, and invalidate the extracted file index.
     */
    public function fromFile(SplFileInfo $sourceFile): void {
        $this->file = $sourceFile;
        
        if (! $this->file->isReadable()) {
            throw new RuntimeException("Cannot read archive source file '$sourceFile'!");
        }
        
        $this->size = $this->file->getSize();
        $this->timestamp = date(DateTimeFormatter::FORMAT_DATETIME, $this->file->getMTime());
        $this->fileHash = $this->name . DIRECTORY_SEPARATOR . md5_file((string) $this->file);
        
        $dir = [];
        $dir[] = ServerEnvironment::getCacheDirectory();
        $dir[] = 'slothsoft/savegame';
        $dir[] = $this->path;
        $dir[] = $this->fileHash;
        $dir[] = 'archive';
        
        $this->extractDirectory = FileInfoFactory::createFromPath(implode(DIRECTORY_SEPARATOR, $dir));
        $this->extractedFiles = null;
    }
}
