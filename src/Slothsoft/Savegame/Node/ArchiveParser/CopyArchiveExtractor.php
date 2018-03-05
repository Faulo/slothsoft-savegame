<?php
namespace Slothsoft\Savegame\Node\ArchiveExtractor;

class CopyArchiveExtractor implements ArchiveExtractorInterface
{

    public function extractArchive(string $archivePath, string $targetDirectory) : bool
    {
        return copy($archivePath, $targetDirectory . DIRECTORY_SEPARATOR . '1');
    }
}

