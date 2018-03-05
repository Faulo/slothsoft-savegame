<?php
namespace Slothsoft\Savegame\Node\ArchiveExtractor;

interface ArchiveBuilderInterface
{
    public function buildArchive(array $buildChildren) : string;
}

