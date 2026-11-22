<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

class Event extends AbstractElement {
    
    /** @var list<Line> */
    protected $lineList;
    
    /**
     * @return void
     */
    protected function init() {
        $this->lineList = [];
    }
    
    /**
     * Reset the line list while binary event parsing is unimplemented.
     */
    public function fromBinary($binary) {
        $this->init();
    }
    
    /**
     * Reset the line list while text event parsing is unimplemented.
     */
    public function fromCode($code) {
        $this->init();
    }
    
    /**
     * Concatenate the binary representations of the event's lines in order.
     */
    public function toBinary() {
        $ret = '';
        foreach ($this->lineList as $line) {
            $ret .= $line->toBinary();
        }
        return $ret;
    }
    
    /**
     * Concatenate the text representations of the event's lines in order.
     */
    public function toCode() {
        $ret = '';
        foreach ($this->lineList as $line) {
            $ret .= $line->toCode();
        }
        return $ret;
    }
}