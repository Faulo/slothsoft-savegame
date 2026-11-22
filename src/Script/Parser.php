<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

class Parser {
    
    /**
     * Load a script from bytes and return its text representation.
     */
    public function binary2code(string $binary): string {
        $script = new Script($this);
        $script->fromBinary($binary);
        return $script->toCode();
    }
    
    /**
     * Load a script from text and return its binary representation.
     */
    public function code2binary(string $code): string {
        $script = new Script($this);
        $script->fromCode($code);
        return $script->toBinary();
    }
}
