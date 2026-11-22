<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Script;

class Event extends AbstractElement {
    
    /** @var list<Line> */
    protected array $lineList = [];
    
    protected function init(): void {
        $this->lineList = [];
    }
    
    /**
     * Reset the line list while binary event parsing is unimplemented.
     */
    public function fromBinary(string $binary): void {
        $this->init();
    }
    
    /**
     * Reset the line list while text event parsing is unimplemented.
     */
    public function fromCode(string $code): void {
        $this->init();
    }
    
    /**
     * Concatenate the binary representations of the event's lines in order.
     */
    public function toBinary(): string {
        $ret = '';
        foreach ($this->lineList as $line) {
            $ret .= $line->toBinary();
        }
        return $ret;
    }
    
    /**
     * Concatenate the text representations of the event's lines in order.
     */
    public function toCode(): string {
        $ret = '';
        foreach ($this->lineList as $line) {
            $ret .= $line->toCode();
        }
        return $ret;
    }
}
