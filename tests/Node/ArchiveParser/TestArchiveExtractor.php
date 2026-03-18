<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node\ArchiveParser;

use Slothsoft\Core\FileSystem;
use SplFileInfo;

final class TestArchiveExtractor implements ArchiveExtractorInterface {
    
    public function extractArchive(SplFileInfo $archivePath, SplFileInfo $targetDirectory): bool {
        FileSystem::ensureDirectory((string) $targetDirectory);
        foreach (file((string) $archivePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            file_put_contents($targetDirectory . DIRECTORY_SEPARATOR . $line, $line);
        }
        return true;
    }
}

