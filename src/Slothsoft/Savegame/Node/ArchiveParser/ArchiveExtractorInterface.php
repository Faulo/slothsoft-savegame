<?php
namespace Slothsoft\Savegame\Node\ArchiveExtractor;

interface ArchiveExtractorInterface
{
    public function extractArchive(string $archiveType, string $archivePath, string $targetDirectory) : bool;
}

