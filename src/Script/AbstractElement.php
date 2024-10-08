<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Script;

use Slothsoft\Savegame\Converter;

abstract class AbstractElement {

    abstract public function fromBinary($binary);

    abstract public function fromCode($code);

    abstract public function toBinary();

    abstract public function toCode();

    protected $ownerParser;

    protected $converter;

    public function __construct(Parser $parser) {
        $this->ownerParser = $parser;
        $this->converter = Converter::getInstance();
    }
}