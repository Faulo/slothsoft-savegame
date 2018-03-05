<?php
namespace Slothsoft\Savegame\Node\ArchiveExtractor;

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

