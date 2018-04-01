<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuilderInterface;

class IntegerValue extends AbstractValueContent
{

    const MAX_VALUES = [
        0,
        256,
        65536,
        16777216,
        4294967296
    ];

    private $min;

    private $max;

    public function getBuildTag(): string
    {
        return 'integer';
    }

    public function getBuildAttributes(BuilderInterface $builder): array
    {
        return parent::getBuildAttributes($builder) + [
            'value' => $this->value,
            'min' => $this->min,
            'max' => $this->max
        ];
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->value = 0;
        $this->min = (int) $strucElement->getAttribute('min');
        $this->max = (int) $strucElement->getAttribute('max', self::MAX_VALUES[$this->size]);
    }

    protected function decodeValue(string $rawValue)
    {
        return $this->getConverter()->decodeInteger($rawValue, $this->size);
    }

    protected function encodeValue($value): string
    {
        return $this->getConverter()->encodeInteger($value, $this->size);
    }
}
