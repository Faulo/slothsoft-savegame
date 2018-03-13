<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Script;



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