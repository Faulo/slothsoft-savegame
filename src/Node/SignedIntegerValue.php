<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

final class SignedIntegerValue extends AbstractValueContent {
    
    const MIN_VALUES = [
        0,
        - 127,
        - 32767,
        - 8388607,
        - 2147483647
    ];
    
    const MAX_VALUES = [
        0,
        127,
        32767,
        8388607,
        2147483647
    ];
    
    private int $min;
    
    private int $max;
    
    public function getBuildTag(): string {
        return 'signed-integer';
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
        $this->min = (int) $strucElement->getAttribute('min', self::MIN_VALUES[$this->size]);
        $this->max = (int) $strucElement->getAttribute('max', self::MAX_VALUES[$this->size]);
    }
    
    protected function decodeValue(string $rawValue) {
        return $this->getConverter()->decodeSignedInteger($rawValue, $this->size);
    }
    
    protected function encodeValue($value): string {
        return $this->getConverter()->encodeSignedInteger($value, $this->size);
    }
}