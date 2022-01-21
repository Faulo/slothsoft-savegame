<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

class StringValue extends AbstractValueContent {

    private $encoding;

    public function getBuildTag(): string {
        return 'string';
    }

    public function getBuildAttributes(BuilderInterface $builder): array {
        return parent::getBuildAttributes($builder) + [
            'value' => $builder->escapeAttribute($this->value),
            'encoding' => $this->encoding
        ];
    }

    protected function loadStruc(LeanElement $strucElement) {
        parent::loadStruc($strucElement);

        $this->value = '';
        $this->encoding = (string) $strucElement->getAttribute('encoding');
    }

    protected function decodeValue(string $rawValue) {
        return $this->getConverter()->decodeString($rawValue, $this->size, $this->encoding);
    }

    protected function encodeValue($value): string {
        return $this->getConverter()->encodeString($value, $this->size, $this->encoding);
    }
}
