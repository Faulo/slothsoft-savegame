<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Build;

use Generator;
use RuntimeException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\IO\Writable\Decorators\ChunkWriterFileCache;
use Slothsoft\Core\IO\Writable\Delegates\ChunkWriterFromChunksDelegate;
use SplFileInfo;

final class XmlBuilder implements BuilderInterface {
    
    /** @var array<string, true> */
    private array $attributeBlacklist = [];
    
    private BuildableInterface $root;
    
    private ?string $cacheDirectory = null;
    
    /**
     * Create an XML builder for the supplied root node.
     */
    public function __construct(BuildableInterface $root) {
        $this->root = $root;
    }
    
    /**
     * Set the directory for serialized XML cache files.
     */
    public function setCacheDirectory(string $cacheDirectory): void {
        $this->cacheDirectory = $cacheDirectory;
    }
    
    /**
     * Provide a no-op for legacy cacheable tag registration.
     *
     * @deprecated This method has no effect. Caching depends on the root node's build hash.
     * @param iterable<string> $list
     */
    public function registerTagCachelist(iterable $list): void {
    }
    
    /**
     * Provide a no-op for legacy cacheable tag removal.
     *
     * @deprecated This method has no effect. Caching depends on the root node's build hash.
     */
    public function clearTagCachelist(): void {
    }
    
    /**
     * Provide a no-op for legacy tag filter registration.
     *
     * @param iterable<string> $list
     * @deprecated This method has no effect. Tag filtering is not supported.
     */
    public function registerTagBlacklist(iterable $list): void {
    }
    
    /**
     * Provide a no-op for legacy tag filter removal.
     *
     * @deprecated This method has no effect. Tag filtering is not supported.
     */
    public function clearTagBlacklist(): void {
    }
    
    /**
     * Add the supplied names to the set of attributes omitted from the XML.
     *
     * @param iterable<string> $list
     */
    public function registerAttributeBlacklist(iterable $list): void {
        foreach ($list as $key) {
            $this->attributeBlacklist[$key] = true;
        }
    }
    
    /**
     * Remove all attribute exclusions from the XML builder.
     */
    public function clearAttributeBlacklist(): void {
        $this->attributeBlacklist = [];
    }
    
    /**
     * Escape UTF-8 text for a double-quoted XML attribute.
     */
    public function escapeAttribute(string $data): string {
        return htmlspecialchars($data, ENT_COMPAT | ENT_XML1, 'UTF-8');
    }
    
    /**
     * Yield XML for the root and its descendants, using cached output when a build hash is available.
     */
    public function toChunks(): Generator {
        if (! $this->cacheDirectory) {
            throw new RuntimeException('cacheDirectory must be set');
        }
        $node = $this->root;
        if ($hash = $node->getBuildHash()) {
            $cacheFile = [];
            $cacheFile[] = $this->cacheDirectory;
            foreach ($node->getBuildAncestors() as $ancestor) {
                if ($ancestorHash = $ancestor->getBuildHash()) {
                    $cacheFile[] = $ancestorHash;
                }
            }
            $cacheFile[] = "$hash.xml";
            $cacheFile = implode(DIRECTORY_SEPARATOR, $cacheFile);
            $cacheFile = FileInfoFactory::createFromPath($cacheFile);
            
            $writer = new ChunkWriterFromChunksDelegate(function () use ($node): Generator {
                yield from $this->chunkBuildableXml($node, true);
            });
            $shouldRefreshCacheDelegate = function (SplFileInfo $cacheFile): bool {
                return false;
            };
            $writer = new ChunkWriterFileCache($writer, $cacheFile, $shouldRefreshCacheDelegate);
            yield from $writer->toChunks();
        } else {
            yield from $this->chunkBuildableXml($node, true);
        }
    }
    
    private function chunkBuildableXml(BuildableInterface $node, bool $isRoot = false): Generator {
        $tag = $node->getBuildTag();
        
        if ($isRoot) {
            $ns = DOMHelper::NS_SAVEGAME_EDITOR;
            yield "<$tag xmlns=\"$ns\"";
        } else {
            yield "<$tag";
        }
        
        foreach ($node->getBuildAttributes($this) as $key => $val) {
            if ($val !== '' and ! isset($this->attributeBlacklist[$key])) {
                yield " $key=\"$val\"";
            }
        }
        if ($children = $node->getBuildChildren()) {
            yield ">";
            foreach ($children as $child) {
                yield from $this->chunkBuildableXml($child);
            }
            yield "</$tag>";
        } else {
            yield "/>";
        }
    }
}
