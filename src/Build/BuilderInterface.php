<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Build;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;

interface BuilderInterface extends ChunkWriterInterface {

    public function registerTagBlacklist(iterable $tagList): void;

    public function clearTagBlacklist(): void;

    public function registerAttributeBlacklist(iterable $tagList): void;

    public function clearAttributeBlacklist(): void;

    public function escapeAttribute(string $name): string;
}