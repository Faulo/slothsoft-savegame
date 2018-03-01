<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Amber\ArchiveManager;
use Slothsoft\Core\FileSystem;
use Slothsoft\Savegame\EditorElement;
use DomainException;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
declare(ticks = 1000);

class ArchiveNode extends AbstractNode implements BuildableInterface
{

    const ARCHIVE_TYPE_RAW = 'Raw';

    const ARCHIVE_TYPE_AM2 = 'AM2';

    const ARCHIVE_TYPE_AMBR = 'AMBR';

    const ARCHIVE_TYPE_JH = 'JH';

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
			'name' 		=> $this->name,
			'type'		=> $this->type,
			'path'		=> $this->path,
			'md5'		=> $this->md5,
			'size'		=> $this->size,
			'timestamp'	=> $this->timestamp,
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
        $ambtoolPath = $this->getOwnerEditor()->getConfigValue('ambtoolPath');
        
        switch ($this->type) {
            case self::ARCHIVE_TYPE_AMBR:
            case self::ARCHIVE_TYPE_JH:
                $manager = new ArchiveManager($ambtoolPath);
                $manager->extractArchive($this->archivePath, $this->fileDirectory);
                break;
            case self::ARCHIVE_TYPE_AM2:
            case self::ARCHIVE_TYPE_RAW:
                copy($this->archivePath, $this->fileDirectory . DIRECTORY_SEPARATOR . '1');
                break;
            default:
                throw new DomainException(sprintf('unknown archive type "%s"!', $this->type));
        }
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
			switch ($this->type) {
				case self::ARCHIVE_TYPE_AMBR:
					$header = [];
					$body = [];
					$maxId = 0;
					foreach ($childList as $child) {
						$id = (int) $child->getFileName();
						if ($id > $maxId) {
							$maxId = $id;
						}
						$val = $child->getContent();
						$header[$id] = pack('N', strlen($val));
						$body[$id] = $val;
					}
					for ($id = 1; $id < $maxId; $id ++) {
						if (! isset($header[$id])) {
							$header[$id] = pack('N', 0);
							$body[$id] = '';
						}
					}
					ksort($header);
					ksort($body);
					
					array_unshift($header, 'AMBR' . pack('n', count($body)));
					
					$ret = implode('', $header) . implode('', $body);
					break;
				case self::ARCHIVE_TYPE_JH:
				case self::ARCHIVE_TYPE_AM2:
				case self::ARCHIVE_TYPE_RAW:
					$ret = '';
					foreach ($childList as $child) {
						$ret .= $child->getContent();
					}
					break;
				default:
					throw new DomainException(sprintf('unknown archive type "%s"!', $this->type));
			}
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
            $this->timestamp = date(DATE_DATETIME, FileSystem::changetime($this->archivePath));
            $this->md5 = md5_file($this->archivePath);
            
            $dir = [];
            $dir[] = sys_get_temp_dir();
            $dir[] = str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, __CLASS__);
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
}