<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

class Line extends AbstractElement {
    
    protected function init(): void {
    }
    
    /**
     * Initialize the line as a placeholder for binary parsing.
     */
    public function fromBinary(string $binary): void {
        $this->init();
    }
    
    /**
     * Initialize the line as a placeholder for text parsing.
     */
    public function fromCode(string $code): void {
        $this->init();
    }
    
    /**
     * Return an empty string as the placeholder binary representation.
     */
    public function toBinary(): string {
        $ret = '';
        return $ret;
    }
    
    /**
     * Return an empty string as the placeholder text representation.
     */
    public function toCode(): string {
        $ret = '';
        return $ret;
    }
}