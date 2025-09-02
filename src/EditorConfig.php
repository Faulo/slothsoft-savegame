<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface;
use Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface;
use SplFileInfo;

class EditorConfig {

    public SplFileInfo $sourceDirectory;

    public SplFileInfo $userDirectory;

    public SplFileInfo $cacheDirectory;

    public SplFileInfo $infosetFile;

    /**
     *
     * @var ArchiveExtractorInterface[]
     */
    public array $archiveExtractors;

    /**
     *
     * @var ArchiveBuilderInterface[]
     */
    public array $archiveBuilders;

    public function __construct(SplFileInfo $sourceDirectory, SplFileInfo $userDirectory, SplFileInfo $cacheDirectory, SplFileInfo $infosetFile, array $archiveExtractors, array $archiveBuilders) {
        $this->sourceDirectory = $sourceDirectory;
        $this->userDirectory = $userDirectory;
        $this->cacheDirectory = $cacheDirectory;

        $this->infosetFile = $infosetFile;
        $this->archiveExtractors = $archiveExtractors;
        $this->archiveBuilders = $archiveBuilders;
    }
}

