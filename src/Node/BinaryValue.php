<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Build\BuilderInterface;

class BinaryValue extends AbstractValueContent {

    public function getBuildTag(): string {
        return 'binary';
    }

    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'value' => $builder->escapeAttribute($this->value)
        ];
    }

    protected function decodeValue(string $rawValue) {
        return $this->getConverter()->decodeBinary($rawValue);
    }

    protected function encodeValue($value): string {
        return $this->getConverter()->encodeBinary($value);
    }
}
