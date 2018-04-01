<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\Calendar\DateTimeFormatter;
use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;

class ArchiveNode extends AbstractNode implements BuildableInterface
{

    const NAMESPACE_SEPARATOR = '\\';

    private $path;

    private $name;

    private $type;

    private $timestamp;

    private $md5;

    private $size;

    private $archivePath;

    private $fileDirectory;

    private $filePathList;

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
        
        $editor = $this->getOwnerEditor();
        
        $defaultFile = $editor->buildDefaultFile($this->path);
        $tempFile = $editor->buildTempFile($this->name);
        
        if ($uploadedArchives = $editor->getConfigValue('uploadedArchives')) {
            if (isset($uploadedArchives[$this->name])) {
                move_uploaded_file($uploadedArchives[$this->name], $tempFile);
            }
        }
        
        $path = file_exists($tempFile) ? $tempFile : $defaultFile;
        
        $this->setArchivePath($path);
    }

    protected function loadNode(EditorElement $strucElement)
    {
        $this->filePathList = [];
        if ($this->getOwnerEditor()->shouldLoadArchive($this->name)) {
            if ($this->fileDirectory) {
                $list = FileSystem::scanDir($this->fileDirectory, FileSystem::SCANDIR_REALPATH);
                if (! count($list)) {
                    $this->loadArchive();
                    $list = FileSystem::scanDir($this->fileDirectory, FileSystem::SCANDIR_REALPATH);
                }
                foreach ($list as $path) {
                    $this->filePathList[$path] = basename($path);
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

    protected function loadArchive()
    {
        $this->getArchiveExtractor()->extractArchive($this->archivePath, $this->fileDirectory);
    }

    public function writeArchive()
    {
        $path = $this->getOwnerEditor()->buildTempFile($this->name);
        $ret = file_put_contents($path, $this->getArchive());
        if ($ret) {
            $this->setArchivePath($path);
        }
        return $ret;
    }

    public function getArchive()
    {
        $ret = null;
        if ($childList = $this->getBuildChildren()) {
            $ret = $this->getArchiveBuilder()->buildArchive($childList);
        }
        return $ret;
    }

    public function getArchiveId()
    {
        return $this->name;
    }

    protected function setArchivePath($path)
    {
        $this->archivePath = $path;
        
        if (file_exists($this->archivePath)) {
            $this->size = FileSystem::size($this->archivePath);
            $this->timestamp = date(DateTimeFormatter::FORMAT_DATETIME, FileSystem::changetime($this->archivePath));
            $this->md5 = md5_file($this->archivePath);
            
            $dir = [];
            $dir[] = sys_get_temp_dir();
            $dir[] = str_replace(self::NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, __CLASS__);
            $dir[] = $this->name;
            $dir[] = $this->md5;
            
            $this->fileDirectory = implode(DIRECTORY_SEPARATOR, $dir);
            
            if (! is_dir($this->fileDirectory)) {
                mkdir($this->fileDirectory, 0777, true);
            }
        }
    }

    public function getFileNameList(): array
    {
        return array_values($this->filePathList);
    }

    public function getFilePathByName(string $name): string
    {
        return array_search($name, $this->filePathList, true);
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
}