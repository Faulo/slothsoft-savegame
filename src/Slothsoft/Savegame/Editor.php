<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use Ds\Vector;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Savegame\Build\XmlBuilder;
use Slothsoft\Savegame\Node\AbstractNode;
use Slothsoft\Savegame\Node\ArchiveNode;
use Slothsoft\Savegame\Node\BinaryValue;
use Slothsoft\Savegame\Node\BitFieldInstruction;
use Slothsoft\Savegame\Node\BitValue;
use Slothsoft\Savegame\Node\EventDictionaryInstruction;
use Slothsoft\Savegame\Node\EventInstruction;
use Slothsoft\Savegame\Node\EventScriptValue;
use Slothsoft\Savegame\Node\EventStepInstruction;
use Slothsoft\Savegame\Node\FileContainer;
use Slothsoft\Savegame\Node\ForEachFileNode;
use Slothsoft\Savegame\Node\GroupContainer;
use Slothsoft\Savegame\Node\InstructionContainer;
use Slothsoft\Savegame\Node\IntegerValue;
use Slothsoft\Savegame\Node\RepeatGroupInstruction;
use Slothsoft\Savegame\Node\SavegameNode;
use Slothsoft\Savegame\Node\SelectValue;
use Slothsoft\Savegame\Node\SignedIntegerValue;
use Slothsoft\Savegame\Node\StringDictionaryInstruction;
use Slothsoft\Savegame\Node\StringValue;
use Slothsoft\Savegame\Node\UseGlobalInstruction;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DomainException;
use RuntimeException;
use UnexpectedValueException;

class Editor implements DOMWriterInterface
{

    private $config = [
        'structureFile' => '',
        'defaultDir' => '',
        'tempDir' => '',
        'id' => '',
        'mode' => '',
        'loadAllArchives' => false,
        'selectedArchives' => [],
        'uploadedArchives' => [],
        'archiveExtractors' => [],
        'archiveBuilders' => []
    ];

    /**
     *
     * @var DOMHelper
     */
    private $dom;

    /**
     *
     * @var AbstractNode
     */
    private $savegame;

    public function __construct(array $config = [])
    {
        foreach ($this->config as $key => &$val) {
            if (isset($config[$key])) {
                $val = $config[$key];
            }
        }
        unset($val);
        if (! $this->config['defaultDir']) {
            throw new RuntimeException('Missing directory for: default saves');
        }
        if (! $this->config['tempDir']) {
            throw new RuntimeException('Missing directory for: temp saves');
        }
        if (! $this->config['mode']) {
            throw new RuntimeException('Missing editor mode');
        }
        if (! $this->config['id']) {
            $this->config['id'] = md5((string) time());
        }
        
        $this->dom = new DOMHelper();
    }

    private function loadDocument(string $structureFile): EditorElement
    {
        $strucDoc = $this->dom->load($structureFile);
        
        if (! ($strucDoc and $strucDoc->documentElement)) {
            throw new UnexpectedValueException("Structure document '$structureFile' is empty.");
        }
        
        if ($strucDoc->xinclude() === - 1) {
            throw new UnexpectedValueException("XInclude processing in the structure document '$structureFile' failed.");
        }
        
        return $this->loadDocumentElement($strucDoc->documentElement);
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

    public function load()
    {
        $rootElement = $this->loadDocument($this->config['structureFile']);
        
        $rootElement->setAttribute('save-id', $this->config['id']);
        $rootElement->setAttribute('save-mode', $this->config['mode']);
        
        $this->savegame = $this->createNode(null, $rootElement);
    }

    public function buildDefaultFile($name)
    {
        // return sprintf('%s%s%s.%s', $this->config['defaultDir'], DIRECTORY_SEPARATOR, $this->config['mode'], $name);
        return sprintf('%s/%s', $this->config['defaultDir'], $name);
    }

    public function buildTempFile($name)
    {
        return sprintf('%s/%s.%s', $this->config['tempDir'], $this->config['id'], $name);
    }

    public function getConfigValue($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    public function getArchiveExtractor(string $type): ArchiveExtractorInterface
    {
        if (! isset($this->config['archiveExtractors'][$type])) {
            throw new DomainException(sprintf('unknown archiveExtractor type "%s"! currently available: %s', $type, implode(', ', array_keys($this->config['archiveExtractors']))));
        }
        return $this->config['archiveExtractors'][$type];
    }

    public function getArchiveBuilder(string $type): ArchiveBuilderInterface
    {
        if (! isset($this->config['archiveBuilders'][$type])) {
            throw new DomainException(sprintf('unknown archiveBuilder type "%s"! currently available: %s', $type, implode(', ', array_keys($this->config['archiveExtractors']))));
        }
        return $this->config['archiveBuilders'][$type];
    }

    public function shouldLoadArchive($name): bool
    {
        return ($this->config['loadAllArchives'] or isset($this->config['selectedArchives'][$name]));
    }

    public function writeArchiveFile($name)
    {
        $ret = null;
        if ($archive = $this->getArchiveById($name)) {
            $ret = $archive->writeArchive();
        }
        return $ret;
    }

    public function getArchiveFile($name)
    {
        $ret = null;
        if ($archive = $this->getArchiveById($name)) {
            $ret = HTTPFile::createFromString($archive->getArchive(), $name);
        }
        return $ret;
    }

    public function parseRequest(array $req)
    {
        if (isset($req['data'])) {
            $valueMap = $this->savegame->getValueMap();
            foreach ($req['data'] as $id => $val) {
                if ($val === '_checkbox') {
                    $val = isset($req['data'][$id . $val]);
                }
                if (isset($valueMap[$id])) {
                    // printf('%s: %s => %s%s', $id, $node->getValue(), $val, PHP_EOL);
                    $valueMap[$id]->setValue($val, true);
                }
            }
        }
    }

    public function getSavegame()
    {
        return $this->savegame;
    }

    public function getArchiveById(string $id): ArchiveNode
    {
        return $this->savegame->getArchiveById($id);
    }

    /**
     *
     * @return HTTPFile
     */
    public function asFile(): HTTPFile
    {
        $builder = new XmlBuilder();
        $handle = $builder->buildStream($this->savegame);
        $ret = HTTPFile::createFromStream($handle, sprintf('savegame.%s.xml', $this->config['id']));
        fclose($handle);
        return $ret;
    }

    public function asString(): string
    {
        $builder = new XmlBuilder();
        $builder->registerAttributeBlacklist([
            'position',
            'bit',
            'encoding'
        ]);
        return $builder->buildString($this->savegame);
    }

    public function asDocument(): DOMDocument
    {
        $ret = new DOMDocument('1.0', 'UTF-8');
        $ret->appendChild($this->asNode($ret));
        return $ret;
    }

    public function asNode(DOMDocument $dataDoc): DOMDocumentFragment
    {
        $retFragment = $dataDoc->createDocumentFragment();
        $retFragment->appendXML($this->asString());
        return $retFragment;
    }

    /**
     *
     * @param \Slothsoft\Savegame\Node\AbstractNode $parentValue
     * @param \Slothsoft\Savegame\EditorElement $strucElement
     * @return \Slothsoft\Savegame\Node\AbstractNode
     */
    public function createNode(AbstractNode $parentValue = null, EditorElement $strucElement): AbstractNode
    {
        $value = $this->constructValue($strucElement->getType());
        $value->init($strucElement, $parentValue);
        return $value;
    }

    private function constructValue(int $type): AbstractNode
    {
        switch ($type) {
            // root
            case EditorElement::NODE_TYPES['savegame.editor']:
                return new SavegameNode($this);
            case EditorElement::NODE_TYPES['archive']:
                return new ArchiveNode();
            case EditorElement::NODE_TYPES['for-each-file']:
                return new ForEachFileNode();
            case EditorElement::NODE_TYPES['file']:
                return new FileContainer();
            
            // values
            case EditorElement::NODE_TYPES['integer']:
                return new IntegerValue();
            case EditorElement::NODE_TYPES['signed-integer']:
                return new SignedIntegerValue();
            case EditorElement::NODE_TYPES['string']:
                return new StringValue();
            case EditorElement::NODE_TYPES['bit']:
                return new BitValue();
            case EditorElement::NODE_TYPES['select']:
                return new SelectValue();
            case EditorElement::NODE_TYPES['event-script']:
                return new EventScriptValue();
            case EditorElement::NODE_TYPES['binary']:
                return new BinaryValue();
            
            // containers
            case EditorElement::NODE_TYPES['group']:
                return new GroupContainer();
            case EditorElement::NODE_TYPES['instruction']:
                return new InstructionContainer();
            
            // instructions
            case EditorElement::NODE_TYPES['bit-field']:
                return new BitFieldInstruction();
            case EditorElement::NODE_TYPES['string-dictionary']:
                return new StringDictionaryInstruction();
            case EditorElement::NODE_TYPES['event-dictionary']:
                return new EventDictionaryInstruction();
            case EditorElement::NODE_TYPES['event']:
                return new EventInstruction();
            case EditorElement::NODE_TYPES['event-step']:
                return new EventStepInstruction();
            case EditorElement::NODE_TYPES['repeat-group']:
                return new RepeatGroupInstruction();
            case EditorElement::NODE_TYPES['use-global']:
                return new UseGlobalInstruction();
            
            default:
                throw new DomainException(sprintf('unknown type: "%s"', $type));
        }
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $fragment = $this->asNode($targetDoc);
        return $fragment->removeChild($fragment->firstChild);
    }

    public function toDocument(): DOMDocument
    {
        return $this->asDocument();
    }
}