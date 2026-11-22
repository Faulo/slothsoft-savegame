<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node\ArchiveParser;

interface ArchiveBuilderInterface {
    
    /**
     * Assemble archive bytes from the supplied file nodes.
     *
     * @param iterable<\Slothsoft\Savegame\Node\FileContainer> $buildChildren
     */
    public function buildArchive(iterable $buildChildren): string;
}

