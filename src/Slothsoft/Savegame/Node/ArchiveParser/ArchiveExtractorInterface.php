<?php
namespace Slothsoft\Savegame\Node\ArchiveParser;

interface ArchiveExtractorInterface
{

    public function extractArchive(string $archivePath, string $targetDirectory): bool;
}

