<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Build;

interface BuildableInterface {
    
    public function getBuildTag(): string;
    
    public function getBuildHash(): string;
    
    /**
     * @return array<string, scalar>
     */
    public function getBuildAttributes(BuilderInterface $builder): array;
    
    /**
     * @return iterable<BuildableInterface|\Slothsoft\Savegame\Node\AbstractNode>
     */
    public function getBuildAncestors(): iterable;
    
    /**
     * @return iterable<BuildableInterface>|null
     */
    public function getBuildChildren(): ?iterable;
    
    public function appendBuildChild(BuildableInterface $childNode): void;
}
