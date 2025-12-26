<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use SplFileInfo;

final class EditorConfig {
    
    public SplFileInfo $sourceDirectory;
    
    public SplFileInfo $userDirectory;
    
    public SplFileInfo $cacheDirectory;
    
    public SplFileInfo $infosetFile;
    
    /**
     *
     * @var \Slothsoft\Savegame\Node\ArchiveParser\ArchiveExtractorInterface[]
     */
    public array $archiveExtractors;
    
    /**
     *
     * @var \Slothsoft\Savegame\Node\ArchiveParser\ArchiveBuilderInterface[]
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

