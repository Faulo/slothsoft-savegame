<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

final class IntegerValue extends AbstractValueContent {
    
    const MAX_VALUES = [
        0,
        256,
        65536,
        16777216,
        4294967296
    ];
    
    private int $min;
    
    private int $max;
    
    public function getBuildTag(): string {
        return 'integer';
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'value' => $this->value,
            'min' => $this->min,
            'max' => $this->max
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->value = 0;
        $this->min = (int) $strucElement->getAttribute('min');
        $this->max = (int) $strucElement->getAttribute('max', self::MAX_VALUES[$this->size]);
    }
    
    protected function decodeValue(string $rawValue) {
        return $this->getConverter()->decodeInteger($rawValue, $this->size);
    }
    
    protected function encodeValue($value): string {
        return $this->getConverter()->encodeInteger($value, $this->size);
    }
}
