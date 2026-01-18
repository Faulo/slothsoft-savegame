<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;
use DomainException;

final class StringValue extends AbstractValueContent {
    
    private string $encoding;
    
    private string $type;
    
    public function getBuildTag(): string {
        return 'string';
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'value' => $builder->escapeAttribute($this->value),
            'encoding' => $this->encoding
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->value = '';
        $this->encoding = (string) $strucElement->getAttribute('encoding');
        $this->type = $strucElement->getAttribute('type', 'size-fixed');
        
        switch ($this->type) {
            case 'null-delimited':
                $text = $this->ownerFile->extractContent($this->contentOffset, 'auto');
                $this->size = strlen($text);
                break;
            case 'size-fixed':
                break;
            default:
                throw new DomainException("Unknown type '$this->type'");
        }
    }
    
    protected function decodeValue(string $rawValue) {
        return $this->getConverter()->decodeString($rawValue, $this->size, $this->encoding);
    }
    
    protected function encodeValue($value): string {
        return $this->getConverter()->encodeString($value, $this->size, $this->encoding);
    }
}
