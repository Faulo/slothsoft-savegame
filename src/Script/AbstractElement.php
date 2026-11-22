<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

use Slothsoft\Savegame\Converter;

abstract class AbstractElement {
    
    /**
     * Load the script element from its binary representation.
     *
     * @param string $binary
     * @return void
     */
    abstract public function fromBinary($binary);
    
    /**
     * Load the script element from its text representation.
     *
     * @param string $code
     * @return void
     */
    abstract public function fromCode($code);
    
    /**
     * Return the script element's binary representation.
     *
     * @return string
     */
    abstract public function toBinary();
    
    /**
     * Return the script element's text representation.
     *
     * @return string
     */
    abstract public function toCode();
    
    /** @var Parser */
    protected $ownerParser;
    
    /** @var Converter */
    protected $converter;
    
    /**
     * Associate the script element with a parser and the shared converter.
     */
    public function __construct(Parser $parser) {
        $this->ownerParser = $parser;
        $this->converter = Converter::getInstance();
    }
}