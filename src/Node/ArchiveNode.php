<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\Calendar\DateTimeFormatter;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\IO\Readable\FileReaderInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use SplFileInfo;

class ArchiveNode extends AbstractNode implements BuildableInterface, FileWriterInterface, FileReaderInterface {

    const NAMESPACE_SEPARATOR = '\\';

    private $path;

    private $name;

    private $type;

    private $timestamp;

    private $fileHash;

    private $size;

    private $file;

    private $strucElement;

    private $extractDirectory;

    private $extractedFiles;

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

    protected function loadStruc(LeanElement $strucElement) {
        parent::loadStruc($strucElement);

        $this->path = (string) $strucElement->getAttribute('path');
        $this->name = (string) $strucElement->getAttribute('name', basename($this->path));
        $this->type = (string) $strucElement->getAttribute('type');

        $this->strucElement = $strucElement;

        $this->fromFile($this->getOwnerEditor()
            ->findGameFile($this->path));
    }

    protected function loadNode(LeanElement $strucElement) {}

    protected function loadChildren(LeanElement $strucElement) {}

    public function load(): void {
        if ($this->extractedFiles === null) {
            $this->extractedFiles = [];
            $list = FileSystem::scanDir((string) $this->extractDirectory, FileSystem::SCANDIR_FILEINFO);
            if (! count($list)) {
                $this->extractArchive();
                $list = FileSystem::scanDir((string) $this->extractDirectory, FileSystem::SCANDIR_FILEINFO);
            }
            foreach ($list as $file) {
                $this->extractedFiles[$file->getFilename()] = $file;
            }
            parent::loadChildren($this->strucElement);
        }
    }

    private function extractArchive() {
        $this->getArchiveExtractor()->extractArchive($this->file, $this->extractDirectory);
    }

    public function getArchiveId() {
        return $this->name;
    }

    public function getFileNodes(): iterable {
        return $this->getBuildChildren() ?? [];
    }

    public function getFileNames(): iterable {
        return array_keys($this->extractedFiles);
    }

    public function getFileByName(string $name): SplFileInfo {
        return $this->extractedFiles[$name];
    }

    public function getFileNodeByName(string $name): FileContainer {
        if ($nodeList = $this->getFileNodes()) {
            foreach ($nodeList as $node) {
                if ($node->getFileName() === $name) {
                    return $node;
                }
            }
        }
    }

    public function appendBuildChild(BuildableInterface $childNode) {
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