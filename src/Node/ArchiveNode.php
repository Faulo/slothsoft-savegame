<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\Calendar\DateTimeFormatter;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\IO\Readable\FileReaderInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use DomainException;
use SplFileInfo;

final class ArchiveNode extends AbstractNode implements BuildableInterface, FileWriterInterface, StringWriterInterface, FileReaderInterface {
    
    const NAMESPACE_SEPARATOR = '\\';
    
    private string $path;
    
    private string $name;
    
    private string $type;
    
    private string $timestamp;
    
    private string $fileHash;
    
    private int $size;
    
    private SplFileInfo $file;
    
    private LeanElement $strucElement;
    
    private SplFileInfo $extractDirectory;
    
    private ?array $extractedFiles = null;
    
    public function getBuildTag(): string {
        return 'archive';
    }
    
    public function getBuildHash(): string {
        return $this->fileHash;
    }
    
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
    
    protected function loadNode(LeanElement $strucElement): void {}
    
    protected function loadChildren(LeanElement $strucElement): void {}
    
    public function load(bool $loadFiles = false): void {
        if ($this->extractedFiles === null) {
            $this->extractedFiles = [];
            foreach ($this->getArchiveFiles() as $file) {
                $this->extractedFiles[$file->getFilename()] = $file;
            }
            parent::loadChildren($this->strucElement);
            
            if ($loadFiles) {
                /** @var $fileNode FileContainer */
                foreach ($this->getFileNodes() as $fileNode) {
                    $fileNode->load();
                }
            }
        }
    }
    
    private function getArchiveFiles(): array {
        $directory = (string) $this->extractDirectory;
        if (is_dir($directory)) {
            $list = FileSystem::scanDir($directory, FileSystem::SCANDIR_FILEINFO);
            if (count($list)) {
                return $list;
            }
        }
        
        $this->extractArchive();
        return FileSystem::scanDir($directory, FileSystem::SCANDIR_FILEINFO);
    }
    
    private function extractArchive(): void {
        $this->getArchiveExtractor()->extractArchive($this->file, $this->extractDirectory);
    }
    
    public function getArchiveId(): string {
        return $this->name;
    }
    
    /**
     *
     * @return FileContainer[]
     */
    public function getFileNodes(): iterable {
        return $this->getBuildChildren() ?? [];
    }
    
    public function getFileNames(): iterable {
        return array_keys($this->extractedFiles);
    }
    
    public function getFileByName(string $name): SplFileInfo {
        if (! isset($this->extractedFiles[$name])) {
            throw new DomainException(sprintf('Unknown file "%s"! Currently available from archive "%s": [%s]', $name, $this->file, implode(', ', $this->getFileNames())));
        }
        
        return $this->extractedFiles[$name];
    }
    
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
    
    public function getOwnerSavegame(): SavegameNode {
        return $this->getParentNode();
    }
    
    private function getOwnerEditor(): Editor {
        return $this->getOwnerSavegame()->getOwnerEditor();
    }
    
    public function toFile(): SplFileInfo {
        return $this->file;
    }
    
    public function toFileName(): string {
        return $this->name;
    }
    
    public function toString(): string {
        if ($childList = $this->getBuildChildren()) {
            return $this->getArchiveBuilder()->buildArchive($childList);
        } else {
            return '';
        }
    }
    
    public function fromString(string $sourceString): void {
        file_put_contents((string) $this->file, $sourceString);
        $this->fromFile($this->file);
    }
    
    public function fromFile(SplFileInfo $sourceFile): void {
        $this->file = $sourceFile;
        
        if (! $this->file->isReadable()) {
            throw new \RuntimeException("Cannot read archive source file '$sourceFile'!");
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