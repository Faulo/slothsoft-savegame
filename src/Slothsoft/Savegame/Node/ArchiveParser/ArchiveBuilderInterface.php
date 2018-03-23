<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node\ArchiveParser;

interface ArchiveBuilderInterface
{

    public function buildArchive(array $buildChildren): string;
}
