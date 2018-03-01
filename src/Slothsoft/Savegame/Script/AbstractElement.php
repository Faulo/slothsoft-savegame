<?php
namespace Slothsoft\Savegame\Script;

use Slothsoft\Savegame\Converter;
declare(ticks = 1000);

abstract class AbstractElement
{

    abstract public function fromBinary($binary);

    abstract public function fromCode($code);

    abstract public function toBinary();

    abstract public function toCode();

    protected $ownerParser;

    protected $converter;

    public function __construct(Parser $parser)
    {
        $this->ownerParser = $parser;
        $this->converter = Converter::getInstance();
    }
}