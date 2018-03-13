<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;



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
