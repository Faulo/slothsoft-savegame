<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuilderInterface;
declare(ticks = 1000);

class SignedIntegerValue extends AbstractValueContent
{

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

    private $min;

    private $max;

    public function getBuildTag(): string
    {
        return 'signed-integer';
    }

    public function getBuildAttributes(BuilderInterface $builder): array
    {
		return parent::getBuildAttributes($builder) + [
			'value' => $this->value,
			'min' 	=> $this->min,
			'max' 	=> $this->max,
		];
	}

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
		$this->value = 0;
        $this->min = (int) $strucElement->getAttribute('min', self::MIN_VALUES[$this->size]);
        $this->max = (int) $strucElement->getAttribute('max', self::MAX_VALUES[$this->size]);
    }

    protected function decodeValue(string $rawValue)
    {
        return $this->getConverter()->decodeSignedInteger($rawValue, $this->size);
    }

    protected function encodeValue($value): string
    {
        return $this->getConverter()->encodeSignedInteger($value, $this->size);
    }
}