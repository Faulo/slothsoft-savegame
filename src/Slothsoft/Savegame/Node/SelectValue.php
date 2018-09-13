<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuilderInterface;

class SelectValue extends AbstractValueContent
{

    protected $dictionaryRef;

    public function getBuildTag(): string
    {
        return 'select';
    }

    public function getBuildAttributes(BuilderInterface $builder): array
    {
        return parent::getBuildAttributes($builder) + [
            'value' => $this->value,
            'dictionary-ref' => $this->dictionaryRef
        ];
    }

    protected function loadStruc(LeanElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->value = 0;
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
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

