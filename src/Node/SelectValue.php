<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;
use InvalidArgumentException;

final class SelectValue extends AbstractValueContent {
    
    protected string $dictionaryRef;
    
    public function getBuildTag(): string {
        return 'select';
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'value' => $this->value,
            'dictionary-ref' => $this->dictionaryRef
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->value = 0;
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
    }
    
    protected function decodeValue(string $rawValue) {
        return $this->getConverter()->decodeInteger($rawValue, $this->size);
    }
    
    protected function encodeValue($value): string {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException("SelectValue must received integer, but got '$value'.");
        }
        return $this->getConverter()->encodeInteger((int) $value, $this->size);
    }
}

