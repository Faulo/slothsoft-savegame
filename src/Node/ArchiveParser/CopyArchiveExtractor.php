<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node\ArchiveParser;

use SplFileInfo;
use Slothsoft\Core\FileSystem;

class CopyArchiveExtractor implements ArchiveExtractorInterface {
    
    public function extractArchive(SplFileInfo $archivePath, SplFileInfo $targetDirectory): bool {
        FileSystem::ensureDirectory((string) $targetDirectory);
        return copy((string) $archivePath, $targetDirectory . DIRECTORY_SEPARATOR . '1');
    }
}

