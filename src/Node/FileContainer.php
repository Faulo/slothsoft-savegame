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

final class FileContainer extends AbstractNode implements NodeEvaluatorInterface, BuildableInterface, FileWriterInterface {
    
    private string $filePath;
    
    private string $fileName;
    
    private ?string $fileHash = null;
    
    private ?string $content = null;
    
    private Vector $valueList;
    
    private ?Vector $imageList = null;
    
    private array $evaluateCache;
    
    private SavegameNode $ownerSavegame;
    
    private LeanElement $strucElement;
    
    public function getBuildTag(): string {
        return 'file';
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return [
            'file-name' => $this->fileName
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $archive = $this->getOwnerArchive();
        
        $this->ownerSavegame = $archive->getOwnerSavegame();
        
        $this->strucElement = $strucElement;
        
        $this->fileName = (string) $strucElement->getAttribute('file-name');
        $this->filePath = (string) $archive->getFileByName($this->fileName);
        
        $this->valueList = new Vector();
        $this->imageList = null;
        $this->evaluateCache = [];
    }
    
    protected function loadChildren(LeanElement $strucElement): void {}
    
    public function load(): void {
        parent::loadChildren($this->strucElement);
    }
    
    protected function loadNode(LeanElement $strucElement): void {
        assert(file_exists($this->filePath), '$this->filePath must exist');
        
        $this->setContent(file_get_contents($this->filePath));
    }
    
    public function extractContent($offset, $length): string {
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
    
    public function insertContent(int $offset, int $length, string $value): void {
        $this->setContent(substr_replace($this->content, $value, $offset, $length));
    }
    
    public function insertContentBit(int $offset, int $bit, bool $value): void {
        // echo "setting bit $bit at position $offset to " . ($value?'ON':'OFF') . PHP_EOL;
        $byte = $this->extractContent($offset, 1);
        $byte = hexdec(bin2hex($byte));
        if ($value) {
            $byte |= $bit;
        } else {
            $byte &= ~ $bit;
        }
        $byte = substr(pack('N', $byte), - 1);
        $this->insertContent($offset, 1, $byte);
    }
    
    public function setContent(string $content): void {
        if ($this->content !== $content) {
            $this->content = $content;
            $this->setDirty();
        }
    }
    
    public function getContent(): string {
        return $this->content;
    }
    
    public function getFileName(): string {
        return $this->fileName;
    }
    
    public function getValueByName(string $name): AbstractValueContent {
        /** @var AbstractValueContent $node */
        foreach ($this->valueList as $node) {
            if ($node->getName() === $name) {
                return $node;
            }
        }
    }
    
    public function getValueById(int $id): AbstractValueContent {
        /** @var AbstractValueContent $node */
        foreach ($this->valueList as $node) {
            if ($node->getValueId() === $id) {
                return $node;
            }
        }
    }
    
    public function getValueList(): Vector {
        return $this->valueList;
    }
    
    public function findStringAtOrAfter(string $search, int $offset = 0): ?int {
        $index = strpos($this->content, $search, $offset);
        return $index === false ? null : $index;
    }
    
    public function evaluate($expression) {
        if (is_int($expression)) {
            return $expression;
        }
        $expression = trim((string) $expression);
        if ($expression === '') {
            return 0;
        }
        $sign = 1;
        while ($expression[0] === '-') {
            $sign *= - 1;
            $expression = substr($expression, 1);
        }
        if (is_numeric($expression)) {
            return $sign * (int) $expression;
        }
        $match = null;
        if (preg_match('/^0x(\w+)$/', $expression, $match)) {
            return $sign * hexdec($match[1]);
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
        return $sign * $this->evaluateCache[$expression];
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
        $this->fileHash ??= $this->fileName . DIRECTORY_SEPARATOR . ($this->content === null ? md5_file($this->filePath) : md5($this->content));
        return $this->fileHash;
    }
    
    public function setDirty(): void {
        $this->fileHash = null;
        $this->getOwnerArchive()->setDirty();
    }
}