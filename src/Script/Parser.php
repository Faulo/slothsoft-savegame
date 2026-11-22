<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

class Parser {
    
    /**
     * Load a script from bytes and return its text representation.
     *
     * @param string $binary
     * @return string
     */
    public function binary2code($binary) {
        $script = new Script($this);
        $script->fromBinary($binary);
        return $script->toCode();
    }
    
    /**
     * Load a script from text and return its binary representation.
     *
     * @param string $code
     * @return string
     */
    public function code2binary($code) {
        $script = new Script($this);
        $script->fromCode($code);
        return $script->toBinary();
    }
}