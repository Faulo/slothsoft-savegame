<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Node\ArchiveParser;

use Slothsoft\Core\FileSystem;
use SplFileInfo;

final class CopyArchiveExtractor implements ArchiveExtractorInterface {
    
    /**
     * Copy the source archive to a file named '1' in the target directory and report success.
     */
    public function extractArchive(SplFileInfo $archivePath, SplFileInfo $targetDirectory): bool {
        FileSystem::ensureDirectory((string) $targetDirectory);
        return copy((string) $archivePath, $targetDirectory . DIRECTORY_SEPARATOR . '1');
    }
}

