<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

use Slothsoft\Savegame\Converter;

abstract class AbstractElement {
    
    /**
     * Load the script element from its binary representation.
     */
    abstract public function fromBinary(string $binary): void;
    
    /**
     * Load the script element from its text representation.
     */
    abstract public function fromCode(string $code): void;
    
    /**
     * Return the script element's binary representation.
     */
    abstract public function toBinary(): string;
    
    /**
     * Return the script element's text representation.
     */
    abstract public function toCode(): string;
    
    protected Parser $ownerParser;
    
    protected Converter $converter;
    
    /**
     * Associate the script element with a parser and the shared converter.
     */
    public function __construct(Parser $parser) {
        $this->ownerParser = $parser;
        $this->converter = Converter::getInstance();
    }
}
