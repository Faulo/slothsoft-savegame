<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Build;

interface BuilderInterface
{

    public function registerTagBlacklist(array $tagList);

    public function clearTagBlacklist();

    public function registerAttributeBlacklist(array $tagList);

    public function clearAttributeBlacklist();

    public function buildStream(BuildableInterface $node);

    public function buildString(BuildableInterface $node): string;

    public function escapeAttribute(string $name): string;
}