<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node\ArchiveParser;

use SplFileInfo;

class CopyArchiveExtractor implements ArchiveExtractorInterface
{

    public function extractArchive(SplFileInfo $archivePath, SplFileInfo $targetDirectory): bool
    {
        if (!$targetDirectory->isDir()) {
            mkdir((string) $targetDirectory, 0777, true);
        }
        return copy((string) $archivePath, $targetDirectory . DIRECTORY_SEPARATOR . '1');
    }
}

