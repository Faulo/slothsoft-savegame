<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Script;

class Parser {

    public function binary2code($binary) {
        $script = new Script($this);
        $script->fromBinary($binary);
        return $script->toCode();
    }

    public function code2binary($code) {
        $script = new Script($this);
        $script->fromCode($code);
        return $script->toBinary();
    }
}