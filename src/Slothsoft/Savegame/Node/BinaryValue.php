<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class BinaryValue extends AbstractValueContent
{

    public function getBuildTag(): string
    {
        return 'binary';
    }

    protected function decodeValue(string $rawValue)
    {
        return $this->getConverter()->decodeBinary($rawValue);
    }

    protected function encodeValue($value): string
    {
        return $this->getConverter()->encodeBinary($value);
    }
}
