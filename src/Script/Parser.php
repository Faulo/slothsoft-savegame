<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

class Parser {
    
    /**
     * @param string $binary
     * @return string
     */
    public function binary2code($binary) {
        $script = new Script($this);
        $script->fromBinary($binary);
        return $script->toCode();
    }
    
    /**
     * @param string $code
     * @return string
     */
    public function code2binary($code) {
        $script = new Script($this);
        $script->fromCode($code);
        return $script->toBinary();
    }
}