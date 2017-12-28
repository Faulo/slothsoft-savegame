<?php
namespace Slothsoft\Savegame\Build;

declare(ticks = 1000);

interface BuildableInterface
{
    public function getBuildTag(): string;

    public function getBuildAttributes(BuilderInterface $builder): array;
	
    public function getBuildChildren();

    public function appendBuildChild(BuildableInterface $childNode);
}