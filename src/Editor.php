<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame;

use DomainException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Node\ArchiveNode;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use Slothsoft\Savegame\Node\FileContainer;
use Slothsoft\Savegame\Node\NodeFactory;
use Slothsoft\Savegame\Node\SavegameNode;
use SplFileInfo;
use UnexpectedValueException;

class Editor {
    
    private EditorConfig $config;
    
    private ?SavegameNode $savegame = null;
    
    /**
     * Create an editor with the supplied savegame paths and archive handlers.
     */
    public function __construct(EditorConfig $config) {
        $this->config = $config;
    }
    
    /**
     * Read the XML structure, resolve XIncludes, and initialize the savegame tree on first use.
     */
    public function load(): void {
        if ($this->savegame === null) {
            $strucDoc = DOMHelper::loadDocument((string) $this->config->infosetFile);
            
            if (! $strucDoc->documentElement) {
                throw new UnexpectedValueException("Structure document is empty.");
            }
            
            if ($strucDoc->xinclude() === -1) {
                throw new UnexpectedValueException("XInclude processing in the structure document failed.");
            }
            
            $rootNode = $strucDoc->documentElement;
            $xml = $strucDoc->saveXML();
            if ($xml === false) {
                throw new UnexpectedValueException('Cannot serialize structure document.');
            }
            $rootNode->setAttribute('file-hash', md5($xml));
            $rootNode->setAttribute('save-id', basename((string) $this->getUserDirectory()));
            
            $rootElement = LeanElement::createTreeFromDOMElement($rootNode);
            $factory = new NodeFactory($this);
            $savegame = $factory->createNode($rootElement, null);
            assert($savegame instanceof SavegameNode);
            $this->savegame = $savegame;
        }
    }
    
    /**
     * Return the configured extractor for the archive type or throw if the type is unknown.
     */
    public function getArchiveExtractor(string $type): ArchiveExtractorInterface {
        if (! isset($this->config->archiveExtractors[$type])) {
            throw new DomainException(sprintf('unknown archiveExtractor type "%s"! currently available: %s', $type, implode(', ', array_keys($this->config->archiveExtractors))));
        }
        return $this->config->archiveExtractors[$type];
    }
    
    /**
     * Return the configured builder for the archive type or throw if the type is unknown.
     */
    public function getArchiveBuilder(string $type): ArchiveBuilderInterface {
        if (! isset($this->config->archiveBuilders[$type])) {
            throw new DomainException(sprintf('unknown archiveBuilder type "%s"! currently available: %s', $type, implode(', ', array_keys($this->config->archiveExtractors))));
        }
        return $this->config->archiveBuilders[$type];
    }
    
    /**
     * Return the savegame tree and optionally load its archives and file contents.
     */
    public function loadSavegame(bool $loadArchives = false, bool $loadFiles = false): SavegameNode {
        $savegameNode = $this->getSavegameNode();
        if ($loadArchives) {
            foreach ($savegameNode->getArchiveNodes() as $archiveNode) {
                $archiveNode->load($loadFiles);
            }
        }
        return $savegameNode;
    }
    
    /**
     * Load the archive at the given path and optionally load its file contents.
     */
    public function loadArchive(string $archivePath, bool $loadFiles = false): ArchiveNode {
        $savegameNode = $this->loadSavegame();
        $archiveNode = $savegameNode->getArchiveByPath($archivePath);
        $archiveNode->load($loadFiles);
        return $archiveNode;
    }
    
    /**
     * Load the named archive and return the file node matching the supplied file ID.
     */
    public function loadFile(string $archivePath, string $fileId): FileContainer {
        $archiveNode = $this->loadArchive($archivePath);
        $fileNode = $archiveNode->getFileNodeByName($fileId);
        return $fileNode;
    }
    
    /**
     * Return the savegame root, loading its structure on first use.
     */
    public function getSavegameNode(): SavegameNode {
        $this->load();
        return $this->savegame ?? throw new UnexpectedValueException('Savegame was not loaded.');
    }
    
    /**
     * Find an archive node by its configured path.
     */
    public function getArchiveNode(string $archivePath): ArchiveNode {
        return $this->getSavegameNode()->getArchiveByPath($archivePath);
    }
    
    /**
     * Find a file node by name within the archive at the supplied path.
     */
    public function getFileNode(string $archiveName, string $fileName): FileContainer {
        return $this->getArchiveNode($archiveName)->getFileNodeByName($fileName);
    }
    
    /**
     * Apply submitted values to matching loaded value nodes by ID and update their file bytes.
     *
     * @param array<int|string, mixed> $data
     */
    public function applyValues(array $data): void {
        $savegame = $this->getSavegameNode();
        
        $valueMap = $savegame->getValueMap();
        foreach ($data as $id => $val) {
            if ($val === '_checkbox') {
                $val = isset($data[$id . $val]);
            }
            if (isset($valueMap[$id])) {
                $node = $valueMap[$id];
                // printf('%s: %s => %s%s', $id, $node->getValue(), $val, PHP_EOL);
                $node->setValue($val, true);
            }
        }
    }
    
    /**
     * Resolve a relative game file path to an existing user file or fall back to the source directory.
     */
    public function findGameFile(string $path): SplFileInfo {
        $defaultFile = $this->buildDefaultFile($path);
        $userFile = $this->buildUserFile($path);
        return $userFile->isFile() ? $userFile : $defaultFile;
    }
    
    /**
     * Write the supplied content under the user directory, creating parent directories as needed.
     */
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
}
