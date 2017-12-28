<?php
namespace Slothsoft\Savegame;

use Ds\Vector;
use Slothsoft\CMS\HTTPFile;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Savegame\Build\XmlBuilder;
use DOMDocument;
use DOMElement;
use DOMNode;
use DomainException;
use RuntimeException;
use UnexpectedValueException;
declare(ticks = 1000);

class Editor
{

    private $config = [
        'structureFile' => '',
        'defaultDir' => '',
        'tempDir' => '',
        'id' => '',
        'mode' => '',
        'ambtoolPath' => '',
        'ambgfxPath' => '',
        'loadAllArchives' => false,
        'selectedArchives' => [],
        'uploadedArchives' => []
    ];

    private $dom;

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
            $this->config['id'] = md5(time());
        }
        
        $this->dom = new DOMHelper();
    }

    private function loadDocument($structureFile)
    {
        $strucDoc = $this->dom->load($structureFile);
        
        if (! ($strucDoc and $strucDoc->documentElement)) {
            throw new UnexpectedValueException('Structure document is empty');
        }
        
        return $this->loadDocumentElement($strucDoc->documentElement);
    }

    private function loadDocumentElement(DOMElement $node)
    {
        $type = EditorElement::getNodeType($node->localName);
        $attributes = [];
        foreach ($node->attributes as $attr) {
            $attributes[$attr->name] = $attr->value;
        }
        $children = [];
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                $children[] = $this->loadDocumentElement($childNode);
            }
        }
        $children = new Vector($children);
        
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
        return sprintf('%s%s%s', $this->config['defaultDir'], DIRECTORY_SEPARATOR, $name);
    }

    public function buildTempFile($name)
    {
        return sprintf('%s%s%s.%s', $this->config['tempDir'], DIRECTORY_SEPARATOR, $this->config['id'], $name);
    }

    public function getConfigValue($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    public function shouldLoadArchive($name)
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
	public function getSavegame() {
		return $this->savegame;
	}
	public function getArchiveById(string $id) : Node\ArchiveNode
    {
		return $this->savegame->getArchiveById($id);
	}

    
    /**
     *
     * @return \Slothsoft\CMS\HTTPFile
     */
    public function asFile() : HTTPFile
    {
		$builder = new XmlBuilder();
		$handle = $builder->buildStream($this->savegame);
        $ret = HTTPFile::createFromStream(
			$handle,
			sprintf('savegame.%s.xml', $this->config['id'])
		);
		fclose($handle);
		return $ret;
    }
    
    public function asString() : string {
		$builder = new XmlBuilder();
		$builder->registerAttributeBlacklist([
			'position',
			'bit',
			'encoding',
		]);
		return $builder->buildString($this->savegame);
    }

    public function asDocument() : DOMDocument
    {
        $ret = new DOMDocument('1.0', 'UTF-8');
        $ret->appendChild($this->asNode($ret));
        return $ret;
    }

    public function asNode(DOMDocument $dataDoc) : DOMNode
    {
        $retFragment = $dataDoc->createDocumentFragment();
        $retFragment->appendXML($this->asString());
        return $retFragment;
    }

    /**
     *
     * @param \Slothsoft\Savegame\Node\AbstractNode $parentValue
     * @param \Slothsoft\Savegame\EditorElement $strucElement
     * @return NULL|\Slothsoft\Savegame\Node\AbstractNode
     */
    public function createNode(Node\AbstractNode $parentValue = null, EditorElement $strucElement)
    {
        if ($value = $this->constructValue($strucElement->getType())) {
            $value->init($strucElement, $parentValue);
            return $value;
        }
    }

    private function constructValue(int $type)
    {
        switch ($type) {
            // root
            case EditorElement::NODE_TYPES['savegame.editor']:
                return new Node\SavegameNode($this);
            case EditorElement::NODE_TYPES['archive']:
                return new Node\ArchiveNode();
            case EditorElement::NODE_TYPES['for-each-file']:
                return new Node\ForEachFileNode();
            case EditorElement::NODE_TYPES['file']:
                return new Node\FileContainer();
            
            // values
            case EditorElement::NODE_TYPES['integer']:
                return new Node\IntegerValue();
            case EditorElement::NODE_TYPES['signed-integer']:
                return new Node\SignedIntegerValue();
            case EditorElement::NODE_TYPES['string']:
                return new Node\StringValue();
            case EditorElement::NODE_TYPES['bit']:
                return new Node\BitValue();
            case EditorElement::NODE_TYPES['select']:
                return new Node\SelectValue();
            case EditorElement::NODE_TYPES['event-script']:
                return new Node\EventScriptValue();
            case EditorElement::NODE_TYPES['binary']:
                return new Node\BinaryValue();
            
            // containers
            case EditorElement::NODE_TYPES['group']:
                return new Node\GroupContainer();
            case EditorElement::NODE_TYPES['instruction']:
                return new Node\InstructionContainer();
            
            // instructions
            case EditorElement::NODE_TYPES['bit-field']:
                return new Node\BitFieldInstruction();
            case EditorElement::NODE_TYPES['string-dictionary']:
                return new Node\StringDictionaryInstruction();
            case EditorElement::NODE_TYPES['event-dictionary']:
                return new Node\EventDictionaryInstruction();
            case EditorElement::NODE_TYPES['event']:
                return new Node\EventInstruction();
            case EditorElement::NODE_TYPES['event-step']:
                return new Node\EventStepInstruction();
            case EditorElement::NODE_TYPES['repeat-group']:
                return new Node\RepeatGroupInstruction();
            case EditorElement::NODE_TYPES['use-global']:
                return new Node\UseGlobalInstruction();
            
            default:
                throw new DomainException(sprintf('unknown type: "%s"', $type));
        }
        return null;
    }
}