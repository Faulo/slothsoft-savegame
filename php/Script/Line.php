<?php
namespace Slothsoft\Savegame\Script;

declare(ticks = 1000);

class Line extends AbstractElement
{

    protected function init()
    {}

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
        return $ret;
    }

    public function toCode()
    {
        $ret = '';
        return $ret;
    }
}