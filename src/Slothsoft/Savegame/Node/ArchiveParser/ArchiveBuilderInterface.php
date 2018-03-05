<?php
namespace Slothsoft\Savegame\Node\ArchiveParser;

interface ArchiveBuilderInterface
{
    public function buildArchive(array $buildChildren) : string;
}

