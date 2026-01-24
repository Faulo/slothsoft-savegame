<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;
use InvalidArgumentException;

final class BitValue extends AbstractValueContent {
    
    private int $bit = 0;
    
    public function getBuildTag(): string {
        return 'bit';
    }
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'value' => $this->value ? '1' : '',
            'bit' => $this->bit
        ];
    }
    
    protected function loadStruc(LeanElement $strucElement): void {
        parent::loadStruc($strucElement);
        
        $this->value = false;
        $this->bit = (int) $strucElement->getAttribute('bit');
    }
    
    private function getBitValue(): int {
        return $this->getConverter()->pow2($this->bit);
    }
    
    public function updateContent(): void {
        $this->ownerFile->insertContentBit($this->contentOffset, $this->getBitValue(), $this->value);
    }
    
    protected function decodeValue(string $rawValue) {
        return (bool) ($this->getConverter()->decodeInteger($rawValue, $this->size) & $this->getBitValue());
    }
    
    protected function encodeValue($value): string {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException("IntegerValue must received integer, but got '$value'.");
        }
        return $this->getConverter()->encodeInteger((int) $value, $this->size);
    }
}