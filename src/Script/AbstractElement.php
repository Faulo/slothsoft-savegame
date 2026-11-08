<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

use Slothsoft\Savegame\Converter;

abstract class AbstractElement {
    
    /**
     * @param string $binary
     * @return void
     */
    abstract public function fromBinary($binary);
    
    /**
     * @param string $code
     * @return void
     */
    abstract public function fromCode($code);
    
    /**
     * @return string
     */
    abstract public function toBinary();
    
    /**
     * @return string
     */
    abstract public function toCode();
    
    /** @var Parser */
    protected $ownerParser;
    
    /** @var Converter */
    protected $converter;
    
    public function __construct(Parser $parser) {
        $this->ownerParser = $parser;
        $this->converter = Converter::getInstance();
    }
}