<?php
namespace Slothsoft\Savegame;

use SplFileInfo;

class EditorConfig
{
    public $sourceDirectory;
    public $userDirectory;
    public $infosetFile;
    public $archiveExtractors;
    public $archiveBuilders;
    public function __construct(SplFileInfo $sourceDirectory, SplFileInfo $userDirectory, SplFileInfo $infosetFile, array $archiveExtractors, array $archiveBuilders) {
        $this->sourceDirectory = $sourceDirectory;
        $this->userDirectory = $userDirectory;
        $this->infosetFile = $infosetFile;
        $this->archiveExtractors = $archiveExtractors;
        $this->archiveBuilders = $archiveBuilders;
    }
}

