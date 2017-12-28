<?php
namespace Slothsoft\Savegame\Script;

declare(ticks = 1000);

class Event extends AbstractElement
{

    protected $lineList;

    protected function init()
    {
        $this->lineList = [];
    }

    public function fromBinary($binary)
    {
        $this->init();
    }

    public function fromCode($code)
    {
        $this->init();
    }

    public function toBinary()
    {
        $ret = '';
        foreach ($this->lineList as $line) {
            $ret .= $line->toBinary();
        }
        return $ret;
    }

    public function toCode()
    {
        $ret = '';
        foreach ($this->lineList as $line) {
            $ret .= $line->toCode();
        }
        return $ret;
    }
}