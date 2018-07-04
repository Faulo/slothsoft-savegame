<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Build;

interface BuildableInterface
{

    public function getBuildTag(): string;

    public function getBuildAttributes(BuilderInterface $builder): array;

    public function getBuildChildren() : ?iterable;

    public function appendBuildChild(BuildableInterface $childNode);
}