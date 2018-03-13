<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\Build\BuilderInterface;
declare(ticks = 1000);

class StringValue extends AbstractValueContent
{

    private $encoding;

    public function getBuildTag(): string
    {
        return 'string';
    }

    public function getBuildAttributes(BuilderInterface $builder): array
    {
        return parent::getBuildAttributes($builder) + [
            'value' => $builder->escapeAttribute($this->value),
            'encoding' => $this->encoding
        ];
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->value = '';
        $this->encoding = (string) $strucElement->getAttribute('encoding');
    }

    protected function decodeValue(string $rawValue)
    {
        return $this->getConverter()->decodeString($rawValue, $this->size, $this->encoding);
    }

    protected function encodeValue($value): string
    {
        return $this->getConverter()->encodeString($value, $this->size, $this->encoding);
    }
}
