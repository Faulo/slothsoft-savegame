<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node\ArchiveParser;

class CopyArchiveBuilder implements ArchiveBuilderInterface {

    public function buildArchive(iterable $buildChildren): string {
        $ret = '';
        foreach ($buildChildren as $child) {
            $ret .= $child->getContent();
        }
        return $ret;
    }
}

