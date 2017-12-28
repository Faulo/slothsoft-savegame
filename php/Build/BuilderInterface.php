<?php
namespace Slothsoft\Savegame\Build;

declare(ticks = 1000);

interface BuilderInterface
{
	public function registerTagBlacklist(array $tagList);
	public function clearTagBlacklist();
	
	public function registerAttributeBlacklist(array $tagList);
	public function clearAttributeBlacklist();
	
	public function buildStream(BuildableInterface $node);
    public function buildString(BuildableInterface $node) : string;
	
	public function escapeAttribute(string $name) : string;
}