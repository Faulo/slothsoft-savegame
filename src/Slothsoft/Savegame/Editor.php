<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use Ds\Vector;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Savegame\Node\ArchiveNode;
use Slothsoft\Savegame\Node\FileContainer;
use Slothsoft\Savegame\Node\NodeFactory;
use Slothsoft\Savegame\Node\SavegameNode;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use DOMDocument;
use DOMElement;
use DomainException;
use SplFileInfo;
use UnexpectedValueException;
use Slothsoft\Core\DOMHelper;

class Editor implements DOMWriterInterface, FileWriterInterface
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
    
    private $loadArchives = [];
    
    public function __construct(EditorConfig $config)
    {
        $this->config = $config;
    }

    public function loadAllArchives()
    {
        $this->loadArchives = true;
        $this->loadDocument();
    }
    
    public function loadNoArchives()
    {
        $this->loadArchives = [];
        $this->loadDocument();
    }
    
    public function loadArchive(string... $archiveIds) {
        $this->loadArchives = $archiveIds;
        $this->loadDocument();
    }

    private function loadDocument(): void
    {
        $strucDoc = DOMHelper::loadDocument((string) $this->config->infosetFile);
        
        if (! ($strucDoc and $strucDoc->documentElement)) {
            throw new UnexpectedValueException("Structure document is empty.");
        }
        
        if ($strucDoc->xinclude() === - 1) {
            throw new UnexpectedValueException("XInclude processing in the structure document failed.");
        }
        
        $rootNode = $strucDoc->documentElement;
        $rootNode->setAttribute('save-id', basename((string) $this->getUserDirectory()));
        
        $rootElement = $this->loadDocumentElement($rootNode);
        $factory = new NodeFactory($this);
        $this->savegame = $factory->createNode($rootElement, null);
    }

    private function loadDocumentElement(DOMElement $node): EditorElement
    {
        $type = EditorElement::getNodeType($node->localName);
        
        $attributes = [];
        foreach ($node->attributes as $attr) {
            $attributes[$attr->name] = $attr->value;
        }
        
        $children = new Vector();
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                $children[] = $this->loadDocumentElement($childNode);
            }
        }
        
        return new EditorElement($type, $attributes, $children);
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
        return $this->getSavegameNode()->getArchiveById($archiveName);
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
    
    
    
    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $this->getSavegameNode()->toElement($targetDoc);
    }
    
    public function toDocument(): DOMDocument
    {
        return $this->getSavegameNode()->toDocument();
    }

    public function toFile(): SplFileInfo
    {
        return $this->getSavegameNode()->toFile();
    }

    public function toString(): string
    {
        return $this->getSavegameNode()->toString();
    }
    
    
    
    
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