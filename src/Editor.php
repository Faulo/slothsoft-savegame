<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Node\ArchiveNode;
use Slothsoft\Savegame\Node\FileContainer;
use Slothsoft\Savegame\Node\NodeFactory;
use Slothsoft\Savegame\Node\SavegameNode;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use DomainException;
use SplFileInfo;
use UnexpectedValueException;

class Editor {
    
    private EditorConfig $config;
    
    private ?SavegameNode $savegame = null;
    
    public function __construct(EditorConfig $config) {
        $this->config = $config;
    }
    
    public function load(): void {
        if ($this->savegame === null) {
            $strucDoc = DOMHelper::loadDocument((string) $this->config->infosetFile);
            
            if (! ($strucDoc and $strucDoc->documentElement)) {
                throw new UnexpectedValueException("Structure document is empty.");
            }
            
            if ($strucDoc->xinclude() === - 1) {
                throw new UnexpectedValueException("XInclude processing in the structure document failed.");
            }
            
            $rootNode = $strucDoc->documentElement;
            $rootNode->setAttribute('file-hash', md5($strucDoc->saveXML()));
            $rootNode->setAttribute('save-id', basename((string) $this->getUserDirectory()));
            
            $rootElement = LeanElement::createTreeFromDOMElement($rootNode);
            $factory = new NodeFactory($this);
            $this->savegame = $factory->createNode($rootElement, null);
        }
    }
    
    public function getArchiveExtractor(string $type): ArchiveExtractorInterface {
        if (! isset($this->config->archiveExtractors[$type])) {
            throw new DomainException(sprintf('unknown archiveExtractor type "%s"! currently available: %s', $type, implode(', ', array_keys($this->config->archiveExtractors))));
        }
        return $this->config->archiveExtractors[$type];
    }
    
    public function getArchiveBuilder(string $type): ArchiveBuilderInterface {
        if (! isset($this->config->archiveBuilders[$type])) {
            throw new DomainException(sprintf('unknown archiveBuilder type "%s"! currently available: %s', $type, implode(', ', array_keys($this->config->archiveExtractors))));
        }
        return $this->config->archiveBuilders[$type];
    }
    
    public function loadSavegame(bool $loadArchives = false, bool $loadFiles = false): SavegameNode {
        $savegameNode = $this->getSavegameNode();
        if ($loadArchives) {
            foreach ($savegameNode->getArchiveNodes() as $archiveNode) {
                $archiveNode->load($loadFiles);
            }
        }
        return $savegameNode;
    }
    
    public function loadArchive(string $archiveId, bool $loadFiles = false): ArchiveNode {
        $savegameNode = $this->loadSavegame();
        $archiveNode = $savegameNode->getArchiveById($archiveId);
        $archiveNode->load($loadFiles);
        return $archiveNode;
    }
    
    public function loadFile(string $archiveId, string $fileId): FileContainer {
        $archiveNode = $this->loadArchive($archiveId);
        $fileNode = $archiveNode->getFileNodeByName($fileId);
        return $fileNode;
    }
    
    public function getSavegameNode(): SavegameNode {
        $this->load();
        return $this->savegame;
    }
    
    public function getArchiveNode(string $archiveName): ArchiveNode {
        return $this->getSavegameNode()->getArchiveById($archiveName);
    }
    
    public function getFileNode(string $archiveName, string $fileName): FileContainer {
        return $this->getArchiveNode($archiveName)->getFileNodeByName($fileName);
    }
    
    public function applyValues(array $data) {
        $valueMap = $this->getSavegameNode()->getValueMap();
        foreach ($data as $id => $val) {
            if ($val === '_checkbox') {
                $val = isset($data[$id . $val]);
            }
            if (isset($valueMap[$id])) {
                // printf('%s: %s => %s%s', $id, $node->getValue(), $val, PHP_EOL);
                $valueMap[$id]->setValue($val, true);
            }
        }
    }
    
    public function findGameFile(string $path): SplFileInfo {
        $defaultFile = $this->buildDefaultFile($path);
        $userFile = $this->buildUserFile($path);
        return $userFile->isFile() ? $userFile : $defaultFile;
    }
    
    public function writeGameFile(string $path, StringWriterInterface $writer): SplFileInfo {
        $userFile = $this->buildUserFile($path);
        $userPath = (string) $userFile;
        if (! is_dir(dirname($userPath))) {
            mkdir(dirname($userPath), 0777, true);
        }
        file_put_contents($userPath, $writer->toString());
        return $userFile;
    }
    
    private function buildDefaultFile(string $name): SplFileInfo {
        return new SplFileInfo($this->getDefaultDirectory() . DIRECTORY_SEPARATOR . $name);
    }
    
    private function getDefaultDirectory(): SplFileInfo {
        return $this->config->sourceDirectory;
    }
    
    private function buildUserFile(string $path): SplFileInfo {
        return new SplFileInfo($this->getUserDirectory() . DIRECTORY_SEPARATOR . $path);
    }
    
    private function getUserDirectory(): SplFileInfo {
        return $this->config->userDirectory;
    }
    
    // public function toDocument(): DOMDocument
    // {
    // return $this->getSavegameNode()->toDocument();
    // }
    // public function toElement(DOMDocument $targetDoc): DOMElement
    // {
    // return $this->getSavegameNode()->toElement($targetDoc);
    // }
    
    // public function toFile(): SplFileInfo
    // {
    // return $this->getSavegameNode()->toFile();
    // }
    // public function toFileName(): string
    // {
    // return $this->getSavegameNode()->toFileName();
    // }
    // public function toString(): string
    // {
    // return $this->getSavegameNode()->toString();
    // }
    
    // private $config = [
    // 'defaultDir' => '',
    // 'userDir' => '',
    // 'id' => '',
    // 'mode' => '',
    // 'loadAllArchives' => false,
    // 'selectedArchives' => [],
    // 'uploadedArchives' => [],
    // 'archiveExtractors' => [],
    // 'archiveBuilders' => []
    // ];
}