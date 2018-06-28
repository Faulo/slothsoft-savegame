<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node\ArchiveParser;

use SplFileInfo;

interface ArchiveExtractorInterface
{

    public function extractArchive(SplFileInfo $archivePath, SplFileInfo $targetDirectory): bool;
}

