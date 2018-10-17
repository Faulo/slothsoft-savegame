<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use Ds\Vector;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Savegame\Node\ArchiveNode;
use Slothsoft\Savegame\Node\FileContainer;
use Slothsoft\Savegame\Node\NodeFactory;
use Slothsoft\Savegame\Node\SavegameNode;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use DOMElement;
use DomainException;
use SplFileInfo;
use UnexpectedValueException;
use Slothsoft\Core\XML\LeanElement;

class Editor
{
    /**
     * @var EditorConfig
     */
    private $config;
    
    /**
     *
     * @var SavegameNode
     */
    private $savegame;
    
    public function __construct(EditorConfig $config)
    {
        $this->config = $config;
    }

    public function loadAllArchives() : void
    {
        $this->load();
        foreach ($this->getArchiveNodes() as $archiveNode) {
            $archiveNode->load();
        }
    }
    public function loadArchive(string $archiveId) : void
    {
        $this->load();
        $this->getArchiveNode($archiveId)->load();
    }

    public function load(): void
    {
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
    public function getArchiveExtractor(string $type): ArchiveExtractorInterface
    {
        if (! isset($this->config->archiveExtractors[$type])) {
            throw new DomainException(sprintf('unknown archiveExtractor type "%s"! currently available: %s', $type, implode(', ', array_keys($this->config->archiveExtractors))));
        }
        return $this->config->archiveExtractors[$type];
    }
    
    public function getArchiveBuilder(string $type): ArchiveBuilderInterface
    {
        if (! isset($this->config->archiveBuilders[$type])) {
            throw new DomainException(sprintf('unknown archiveBuilder type "%s"! currently available: %s', $type, implode(', ', array_keys($this->config->archiveExtractors))));
        }
        return $this->config->archiveBuilders[$type];
    }
    public function getSavegameNode() : SavegameNode {
        return $this->savegame;
    }
    public function getArchiveNode(string $archiveName): ArchiveNode
    {
        $this->load();
        return $this->getSavegameNode()->getArchiveById($archiveName);
    }
    public function getArchiveNodes(): iterable
    {
        $this->load();
        return $this->getSavegameNode()->getArchiveNodes();
    }
    public function getFileNode(string $archiveName, string $fileName): FileContainer
    {
        return $this->getArchiveNode($archiveName)->getFileNodeByName($fileName);
    }
    public function applyValues(array $data)
    {
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
    public function findGameFile(string $name) : SplFileInfo
    {
        $defaultFile = $this->buildDefaultFile($name);
        $userFile = $this->buildUserFile($name);
        return $userFile->isFile() ? $userFile : $defaultFile;
    }
    public function writeGameFile(string $name, FileWriterInterface $writer) : SplFileInfo
    {
        $userFile = $this->buildUserFile($name);
        $userPath = (string) $userFile;
        if (!is_dir(dirname($userPath))) {
            mkdir(dirname($userPath), 0777, true);
        }
        file_put_contents($userPath, $writer->toString());
        return $userFile;
    }
    
    private function buildDefaultFile(string $name) : SplFileInfo
    {
        return new SplFileInfo($this->getDefaultDirectory() . DIRECTORY_SEPARATOR . $name);
    }
    private function getDefaultDirectory() : SplFileInfo {
        return $this->config->sourceDirectory;
    }
    private function buildUserFile(string $name) : SplFileInfo
    {
        return new SplFileInfo($this->getUserDirectory() . DIRECTORY_SEPARATOR . $name);
    }
    private function getUserDirectory() : SplFileInfo {
        return $this->config->userDirectory;
    }

    

    public function shouldLoadArchive($name): bool
    {
        return $this->loadArchives === true or in_array($name, $this->loadArchives);
     }
    
    
     
     
//     public function toDocument(): DOMDocument
//     {
//         return $this->getSavegameNode()->toDocument();
//     }
//     public function toElement(DOMDocument $targetDoc): DOMElement
//     {
//         return $this->getSavegameNode()->toElement($targetDoc);
//     }

//     public function toFile(): SplFileInfo
//     {
//         return $this->getSavegameNode()->toFile();
//     }
//     public function toFileName(): string
//     {
//         return $this->getSavegameNode()->toFileName();
//     }
//     public function toString(): string
//     {
//         return $this->getSavegameNode()->toString();
//     }
    
    
    
    
    //     private $config = [
    //         'defaultDir' => '',
    //         'userDir' => '',
    //         'id' => '',
    //         'mode' => '',
    //         'loadAllArchives' => false,
    //         'selectedArchives' => [],
    //         'uploadedArchives' => [],
    //         'archiveExtractors' => [],
    //         'archiveBuilders' => []
    //     ];
}