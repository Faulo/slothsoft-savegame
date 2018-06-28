<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\Calendar\DateTimeFormatter;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use SplFileInfo;
use Slothsoft\Core\IO\Readable\FileReaderInterface;
use Slothsoft\Core\IO\FileInfoFactory;

class ArchiveNode extends AbstractNode implements BuildableInterface, FileWriterInterface, FileReaderInterface
{

    const NAMESPACE_SEPARATOR = '\\';

    private $path;

    private $name;

    private $type;

    private $timestamp;

    private $md5;

    private $size;

    private $file;

    private $extractDirectory;

    private $extractedFiles;

    public function getBuildTag(): string
    {
        return 'archive';
    }

    public function getBuildAttributes(BuilderInterface $builder): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'path' => $this->path,
            'md5' => $this->md5,
            'size' => $this->size,
            'timestamp' => $this->timestamp
        ];
    }

    public function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->path = (string) $strucElement->getAttribute('path');
        $this->name = (string) $strucElement->getAttribute('name', basename($this->path));
        $this->type = (string) $strucElement->getAttribute('type');
        
        $this->fromFile($this->getOwnerEditor()->findGameFile($this->path));
    }

    protected function loadNode(EditorElement $strucElement)
    {
        $this->extractedFiles = [];
        if ($this->getOwnerEditor()->shouldLoadArchive($this->name)) {
            if ($this->extractDirectory) {
                $list = FileSystem::scanDir((string) $this->extractDirectory, FileSystem::SCANDIR_FILEINFO);
                if (! count($list)) {
                    $this->loadArchive();
                    $list = FileSystem::scanDir((string) $this->extractDirectory, FileSystem::SCANDIR_FILEINFO);
                }
                foreach ($list as $file) {
                    $this->extractedFiles[$file->getFilename()] = $file;
                }
            }
        }
    }

    protected function loadChildren(EditorElement $strucElement)
    {
        if ($this->getOwnerEditor()->shouldLoadArchive($this->name)) {
            parent::loadChildren($strucElement);
        }
    }

    private function loadArchive()
    {
        $this->getArchiveExtractor()->extractArchive($this->file, $this->extractDirectory);
    }

    public function getArchiveId()
    {
        return $this->name;
    }

    public function getFileNames(): iterable
    {
        return array_keys($this->extractedFiles);
    }
    
    public function getFileByName(string $name): SplFileInfo
    {
        return $this->extractedFiles[$name];
    }
    
    
    public function getFileNodeByName(string $name): FileContainer
    {
        if ($nodeList = $this->getBuildChildren()) {
            foreach ($nodeList as $node) {
                if ($node->getFileName() === $name) {
                    return $node;
                }
            }
        }
    }

    public function appendBuildChild(BuildableInterface $childNode)
    {
        assert($childNode instanceof FileContainer);
        
        parent::appendBuildChild($childNode);
    }

    private function getArchiveBuilder(): ArchiveBuilderInterface
    {
        return $this->getOwnerEditor()->getArchiveBuilder($this->type);
    }

    private function getArchiveExtractor(): ArchiveExtractorInterface
    {
        return $this->getOwnerEditor()->getArchiveExtractor($this->type);
    }
    
    public function getOwnerSavegame(): SavegameNode
    {
        return $this->getParentNode();
    }
    private function getOwnerEditor(): Editor
    {
        return $this->getOwnerSavegame()->getOwnerEditor();
    }
    
    public function toFile(): SplFileInfo
    {
        return $this->file;
    }

    public function toString(): string
    {
        if ($childList = $this->getBuildChildren()) {
            return $this->getArchiveBuilder()->buildArchive($childList);
        } else {
            return '';
        }
    }
    
    public function fromString(string $sourceString) : void
    {
        file_put_contents((string) $this->file, $sourceString);
        $this->fromFile($this->file);
    }

    public function fromFile(SplFileInfo $sourceFile) : void
    {
        $this->file = $sourceFile;
        
        if ($this->file->isReadable()) {
            $this->size = $this->file->getSize();
            $this->timestamp = date(DateTimeFormatter::FORMAT_DATETIME, $this->file->getMTime());
            $this->md5 = md5_file((string) $this->file);
            
            $dir = [];
            $dir[] = ServerEnvironment::getCacheDirectory();
            $dir[] = str_replace(self::NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, __CLASS__);
            $dir[] = $this->name;
            $dir[] = $this->md5;
            
            $this->extractDirectory = FileInfoFactory::createFromPath(implode(DIRECTORY_SEPARATOR, $dir));
            
            if (! $this->extractDirectory->isDir()) {
                mkdir((string) $this->extractDirectory, 0777, true);
            }
        }
    }



}