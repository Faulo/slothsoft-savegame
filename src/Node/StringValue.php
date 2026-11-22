<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use DomainException;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

final class StringValue extends AbstractValueContent {
    
    private string $encoding;
    
    private string $type;
    
    /**
     * Return the XML tag name for a string field.
     */
    public function getBuildTag(): string {
        return 'string';
    }
    
    /**
     * Include the escaped text value and its encoding in the XML attributes.
     */
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
    
    protected function decodeValue(string $rawValue): string {
        return $this->getConverter()->decodeString($rawValue, $this->size, $this->encoding);
    }
    
    protected function encodeValue(mixed $value): string {
        return $this->getConverter()->encodeString($value, $this->size, $this->encoding);
    }
}
