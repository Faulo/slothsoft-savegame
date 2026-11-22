<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

class Line extends AbstractElement {
    
    /**
     * @return void
     */
    protected function init() {
    }
    
    /**
     * Initialize the line as a placeholder for binary parsing.
     */
    public function fromBinary($binary) {
        $this->init();
    }
    
    /**
     * Initialize the line as a placeholder for text parsing.
     */
    public function fromCode($code) {
        $this->init();
    }
    
    /**
     * Return an empty string as the placeholder binary representation.
     */
    public function toBinary() {
        $ret = '';
        return $ret;
    }
    
    /**
     * Return an empty string as the placeholder text representation.
     */
    public function toCode() {
        $ret = '';
        return $ret;
    }
}