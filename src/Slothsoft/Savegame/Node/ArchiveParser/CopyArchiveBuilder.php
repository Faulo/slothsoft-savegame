<?php
namespace Slothsoft\Savegame\Node\ArchiveParser;

class CopyArchiveBuilder implements ArchiveBuilderInterface
{
    public function buildArchive(array $buildChildren) : string {
        $ret = '';
        foreach ($childList as $child) {
            $ret .= $child->getContent();
        }
        return $ret;
    }
}

