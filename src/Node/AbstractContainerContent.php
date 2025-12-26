<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Savegame\Build\BuildableInterface;
use Slothsoft\Savegame\Build\BuilderInterface;

abstract class AbstractContainerContent extends AbstractContentNode implements BuildableInterface {
    
    protected function loadContent(LeanElement $strucElement): void {}
    
    public function getBuildAttributes(BuilderInterface $builder): array {
        return [
            'name' => $this->name
        ];
    }
}
