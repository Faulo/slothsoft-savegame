<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use Slothsoft\Core\XML\LeanElement;
use SplFileInfo;

class ArchiveRepository {
    
    public function __construct(SplFileInfo $sourceDirectory, LeanElement $infosetElement) {}
}

