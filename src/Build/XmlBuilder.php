<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Build;

use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\IO\Writable\Decorators\ChunkWriterFileCache;
use Slothsoft\Core\IO\Writable\Delegates\ChunkWriterFromChunksDelegate;
use Generator;
use RuntimeException;
use SplFileInfo;

class XmlBuilder implements BuilderInterface {

    private $tagCachelist = [];

    private $tagBlacklist = [];

    private $attributeBlacklist = [];

    private $root;

    private $cacheDirectory;

    public function __construct(BuildableInterface $root) {
        $this->root = $root;
    }

    public function setCacheDirectory(string $cacheDirectory): void {
        $this->cacheDirectory = $cacheDirectory;
    }

    public function registerTagCachelist(iterable $list): void {
        foreach ($list as $key) {
            $this->tagCachelist[$key] = true;
        }
    }

    public function clearTagCachelist(): void {
        $this->tagCachelist = [];
    }

    public function registerTagBlacklist(iterable $list): void {
        foreach ($list as $key) {
            $this->tagBlacklist[$key] = true;
        }
    }

    public function clearTagBlacklist(): void {
        $this->tagBlacklist = [];
    }

    public function registerAttributeBlacklist(iterable $list): void {
        foreach ($list as $key) {
            $this->attributeBlacklist[$key] = true;
        }
    }

    public function clearAttributeBlacklist(): void {
        $this->attributeBlacklist = [];
    }

    public function escapeAttribute(string $data): string {
        return htmlspecialchars($data, ENT_COMPAT | ENT_XML1, 'UTF-8');
    }

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
                yield from $this->chunkBuildableXml($node);
            });
            $shouldRefreshCacheDelegate = function (SplFileInfo $cacheFile): bool {
                return false;
            };
            $writer = new ChunkWriterFileCache($writer, $cacheFile, $shouldRefreshCacheDelegate);
            yield from $writer->toChunks();
        } else {
            yield from $this->chunkBuildableXml($this->root);
        }
    }

    private function chunkBuildableXml(BuildableInterface $node): Generator {
        $tag = $node->getBuildTag();

        yield "<$tag";

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