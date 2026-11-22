<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Build\BuilderInterface;

final class BinaryValue extends AbstractValueContent {
    
    /**
     * Return the XML tag name for a binary field.
     */
    public function getBuildTag(): string {
        return 'binary';
    }
    
    /**
     * Include the field's hexadecimal value in its XML attributes.
     */
    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
                'value' => $builder->escapeAttribute($this->value)
            ];
    }
    
    protected function decodeValue(string $rawValue): string {
        return $this->getConverter()->decodeBinary($rawValue);
    }
    
    protected function encodeValue(mixed $value): string {
        return $this->getConverter()->encodeBinary($value);
    }
}
