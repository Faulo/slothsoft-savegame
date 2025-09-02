<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Ds\Vector;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\NodeEvaluatorInterface;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;
use SplFileInfo;

class FileContainer extends AbstractNode implements NodeEvaluatorInterface, BuildableInterface, FileWriterInterface {

    private $filePath;

    private $fileName;

    private $fileHash;

    /**
     *
     * @var string
     */
    private $content;

    private $valueList;

    private $imageList;

    private $evaluateCache;

    private $ownerSavegame;

    private $strucElement;

    public function getBuildTag(): string {
        return 'file';
    }

    public function getBuildAttributes(BuilderInterface $builder): array {
        return [
            'file-name' => $this->fileName
        ];
    }

    protected function loadStruc(LeanElement $strucElement) {
        parent::loadStruc($strucElement);

        $archive = $this->getOwnerArchive();

        $this->ownerSavegame = $archive->getOwnerSavegame();

        $this->strucElement = $strucElement;

        $this->fileName = (string) $strucElement->getAttribute('file-name');
        $this->filePath = (string) $archive->getFileByName($this->fileName);
        $this->fileHash = $this->fileName . DIRECTORY_SEPARATOR . md5_file($this->filePath);

        $this->valueList = new Vector();
        $this->imageList = null;
        $this->evaluateCache = [];
    }

    protected function loadChildren(LeanElement $strucElement) {}

    public function load(): void {
        parent::loadChildren($this->strucElement);
    }

    protected function loadNode(LeanElement $strucElement) {
        assert(file_exists($this->filePath), '$this->filePath must exist');

        $this->setContent(file_get_contents($this->filePath));
    }

    public function extractContent($offset, $length) {
        $ret = null;
        switch ($length) {
            case 'auto':
                $ret = '';
                for ($i = $offset, $j = strlen($this->content); $i < $j; $i ++) {
                    $char = $this->content[$i];
                    if ($char === "\0") {
                        break;
                    } else {
                        $ret .= $char;
                    }
                }
                break;
            default:
                $ret = (string) substr($this->content, $offset, $length);
                $ret = str_pad($ret, $length, "\0");
                break;
        }
        return $ret;
    }

    public function insertContent($offset, $length, $value) {
        $this->content = substr_replace($this->content, $value, $offset, $length);
    }

    public function insertContentBit($offset, $bit, $value) {
        // echo "setting bit $bit at position $offset to " . ($value?'ON':'OFF') . PHP_EOL;
        $byte = $this->extractContent($offset, 1);
        $byte = hexdec(bin2hex($byte));
        if ($value) {
            $byte |= $bit;
        } else {
            $byte &= ~ $bit;
        }
        $byte = substr(pack('N', $byte), - 1);
        return $this->insertContent($offset, 1, $byte);
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getContent() {
        return $this->content;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function getValueByName(string $name) {
        foreach ($this->valueList as $node) {
            if ($node->getName() === $name) {
                return $node;
            }
        }
    }

    public function getValueById(int $id) {
        foreach ($this->valueList as $node) {
            if ($node->getValueId() === $id) {
                return $node;
            }
        }
    }

    public function getValueList(): Vector {
        return $this->valueList;
    }

    public function evaluate($expression) {
        if (is_int($expression)) {
            return $expression;
        }
        $expression = trim((string) $expression);
        if ($expression === '') {
            return 0;
        }
        if (is_numeric($expression)) {
            return (int) $expression;
        }
        $match = null;
        if (preg_match('/^0x(\w+)$/', $expression, $match)) {
            return hexdec($match[1]);
        }

        if (! isset($this->evaluateCache[$expression])) {
            $matches = null;
            preg_match_all('/\$([A-Za-z0-9\-\.]+)/', $expression, $matches);
            $translate = [];
            foreach ($matches[0] as $i => $key) {
                if ($node = $this->getValueByName($matches[1][$i])) {
                    $val = $node->getValue();
                } else {
                    $val = 0;
                }
                $translate[$key] = $val;
            }
            $code = strtr($expression, $translate);
            $code = trim($code);
            // echo $code . PHP_EOL;
            $this->evaluateCache[$expression] = $this->evaluateMath($code);
            // echo $expression . PHP_EOL . $code . PHP_EOL . $this->evaluateCache[$expression] . PHP_EOL . PHP_EOL;
        }
        return $this->evaluateCache[$expression];
    }

    public function evaluateMath(string $code): int {
        static $evalList = [];
        if (! isset($evalList[$code])) {
            $evalList[$code] = eval("return (int) ($code);");
            // echo $code . PHP_EOL . $evalList[$code] . PHP_EOL . PHP_EOL;
        }
        return $evalList[$code];
    }

    public function registerValue(AbstractValueContent $node): int {
        $this->valueList[] = $node;
        return $this->ownerSavegame->nextValueId();
    }

    public function registerImage(ImageValue $node): int {
        if ($this->imageList === null) {
            $this->imageList = new Vector();
        }
        $id = $this->imageList->count();
        $this->imageList[] = $node;
        return $id;
    }

    public function getImageNodes(): iterable {
        return $this->imageList;
    }

    public function getImageNodeById(int $id): ImageValue {
        return $this->imageList[$id] ?? null;
    }

    public function getOwnerSavegame(): SavegameNode {
        return $this->ownerSavegame;
    }

    private function getOwnerArchive(): ArchiveNode {
        $parent = $this->getParentNode();
        return $parent instanceof ArchiveNode ? $parent : $parent->getParentNode();
    }

    public function toFile(): SplFileInfo {
        return FileInfoFactory::createFromPath($this->filePath);
    }

    public function getBuildHash(): string {
        return $this->fileHash;
    }
}