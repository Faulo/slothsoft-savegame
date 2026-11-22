<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node\ArchiveParser;

final class CopyArchiveBuilder implements ArchiveBuilderInterface {
    
    /**
     * Concatenate the contents of the supplied file nodes in iteration order.
     */
    public function buildArchive(iterable $buildChildren): string {
        $ret = '';
        foreach ($buildChildren as $child) {
            $ret .= $child->getContent();
        }
        return $ret;
    }
}

