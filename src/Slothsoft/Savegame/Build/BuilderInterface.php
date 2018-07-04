<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Build;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;

interface BuilderInterface extends ChunkWriterInterface
{

    public function registerTagBlacklist(iterable $tagList);

    public function clearTagBlacklist();

    public function registerAttributeBlacklist(iterable $tagList);

    public function clearAttributeBlacklist();

//     public function buildStream(BuildableInterface $node);

//     public function buildString(BuildableInterface $node): string;

    public function escapeAttribute(string $name): string;
}