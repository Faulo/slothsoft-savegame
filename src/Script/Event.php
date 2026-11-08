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
    
    public function fromBinary($binary) {
        $this->init();
    }
    
    public function fromCode($code) {
        $this->init();
    }
    
    public function toBinary() {
        $ret = '';
        foreach ($this->lineList as $line) {
            $ret .= $line->toBinary();
        }
        return $ret;
    }
    
    public function toCode() {
        $ret = '';
        foreach ($this->lineList as $line) {
            $ret .= $line->toCode();
        }
        return $ret;
    }
}