<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Build;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;

interface BuilderInterface extends ChunkWriterInterface {
    
    /**
     * Provide a no-op for legacy tag filter registration.
     *
     * @param iterable<string> $tagList
     * @deprecated This method has no effect. Tag filtering is not supported.
     */
    public function registerTagBlacklist(iterable $tagList): void;
    
    /**
     * Provide a no-op for legacy tag filter removal.
     *
     * @deprecated This method has no effect. Tag filtering is not supported.
     */
    public function clearTagBlacklist(): void;
    
    /**
     * Exclude the supplied attribute names from the output.
     *
     * @param iterable<string> $tagList
     */
    public function registerAttributeBlacklist(iterable $tagList): void;
    
    /**
     * Clear the list of excluded attribute names.
     */
    public function clearAttributeBlacklist(): void;
    
    /**
     * Escape a string for use as a serialized attribute value.
     */
    public function escapeAttribute(string $name): string;
}
