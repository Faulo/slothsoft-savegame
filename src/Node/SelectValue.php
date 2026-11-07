<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use InvalidArgumentException;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

final class SelectValue extends AbstractValueContent {
    use IntegerFormatTrait;
    
    protected string $dictionaryRef;
    
    public function getBuildTag(): string {
        return 'select';
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + $this->getIntegerFormatAttributes() + [
                'value' => $this->value,
                'dictionary-ref' => $this->dictionaryRef
            ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        $this->loadIntegerFormat($strucElement);
        
        $this->value = 0;
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
    }
    
    protected function decodeValue(string $rawValue): int {
        return $this->decodeUnsignedInteger($rawValue);
    }
    
    protected function encodeValue($value): string {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException("SelectValue must received integer, but got '$value'.");
        }
        return $this->encodeUnsignedInteger((int) $value);
    }
}

