<?php
declare(strict_types = 1);

namespace Slothsoft\Savegame\Build;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slothsoft\Core\DOMHelper;

final class XmlBuilderTest extends TestCase {
    
    /**
     * @dataProvider rootTags
     */
    public function testSerializeTreeWithNamespaceOnRootOnly(string $rootTag): void {
        $integer = new TestBuildableNode('integer', ['name' => 'counter', 'value' => 0, 'empty' => '']);
        $string = new TestBuildableNode('string', ['value' => 'text']);
        $group = new TestBuildableNode('group', [], [$integer, $string]);
        $builder = $this->createBuilder(new TestBuildableNode($rootTag, [], [$group]));
        
        $xml = $this->serialize($builder);
        $expected = '<' . $rootTag . ' xmlns="http://schema.slothsoft.net/savegame/editor">'
            . '<group><integer name="counter" value="0"/><string value="text"/></group>'
            . '</' . $rootTag . '>';
        $this->assertXmlStringEqualsXmlString($expected, $xml);
        $this->assertSame(1, substr_count($xml, 'xmlns='));
    }
    
    public function rootTags(): iterable {
        yield 'savegame export' => ['savegame'];
        yield 'archive export' => ['archive'];
        yield 'file export' => ['file'];
    }
    
    public function testEscapeAttributeRoundTrip(): void {
        $value = "A & B < C > D \"quoted\" 'single' ä";
        $root = new TestBuildableNode('string', ['value' => $value]);
        
        $document = DOMHelper::parseDocument($this->serialize($this->createBuilder($root)));
        $this->assertSame($value, $document->documentElement->getAttribute('value'));
    }
    
    public function testAttributeBlacklistAccumulatesAndCanBeCleared(): void {
        $child = new TestBuildableNode('string', ['position' => 4, 'encoding' => 'ASCII', 'value' => 0]);
        $root = new TestBuildableNode('group', ['position' => 1, 'name' => 'data'], [$child]);
        $builder = $this->createBuilder($root);
        $builder->registerAttributeBlacklist(['position']);
        $builder->registerAttributeBlacklist(['encoding']);
        
        $this->assertXmlStringEqualsXmlString(
            '<group xmlns="http://schema.slothsoft.net/savegame/editor" name="data"><string value="0"/></group>',
            $this->serialize($builder)
        );
        
        $builder->clearAttributeBlacklist();
        $this->assertXmlStringEqualsXmlString(
            '<group xmlns="http://schema.slothsoft.net/savegame/editor" position="1" name="data">'
            . '<string position="4" encoding="ASCII" value="0"/></group>',
            $this->serialize($builder)
        );
    }
    
    public function testUnhashedRootIsSerializedAgain(): void {
        $root = new TestBuildableNode('integer', ['value' => 1]);
        $builder = $this->createBuilder($root);
        
        $this->assertXmlStringEqualsXmlString(
            '<integer xmlns="http://schema.slothsoft.net/savegame/editor" value="1"/>',
            $this->serialize($builder)
        );
        $root->attributes['value'] = 2;
        $this->assertXmlStringEqualsXmlString(
            '<integer xmlns="http://schema.slothsoft.net/savegame/editor" value="2"/>',
            $this->serialize($builder)
        );
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testCacheIsWrittenAndReusedAcrossBuilders(): void {
        $cacheDirectory = temp_dir(__METHOD__);
        $ancestor = new TestBuildableNode('archive', [], null, 'archive-hash');
        $root = new TestBuildableNode('file', ['file-name' => 'data'], null, 'file-hash', [$ancestor]);
        $xml = $this->serialize($this->createBuilder($root, $cacheDirectory));
        
        $this->assertXmlStringEqualsXmlString(
            '<file xmlns="http://schema.slothsoft.net/savegame/editor" file-name="data"/>',
            $xml
        );
        $cacheFile = $cacheDirectory . DIRECTORY_SEPARATOR . 'archive-hash' . DIRECTORY_SEPARATOR . 'file-hash.xml';
        $this->assertFileExists($cacheFile);
        $this->assertSame($xml, file_get_contents($cacheFile));
        
        $cachedRoot = new TestBuildableNode('file', ['file-name' => 'changed'], null, 'file-hash', [$ancestor]);
        $this->assertSame($xml, $this->serialize($this->createBuilder($cachedRoot, $cacheDirectory)));
        $this->assertSame(0, $cachedRoot->attributeReads);
        $this->assertSame(0, $cachedRoot->childrenReads);
    }
    
    /**
     * @dataProvider changedHashes
     * @runInSeparateProcess
     */
    public function testChangedHashesSelectFreshCache(string $rootHash, string $ancestorHash): void {
        $cacheDirectory = temp_dir(__METHOD__);
        $ancestor = new TestBuildableNode('archive', [], null, 'archive-1');
        $root = new TestBuildableNode('file', ['file-name' => 'old'], null, 'file-1', [$ancestor]);
        $this->serialize($this->createBuilder($root, $cacheDirectory));
        
        $changedAncestor = new TestBuildableNode('archive', [], null, $ancestorHash);
        $changedRoot = new TestBuildableNode('file', ['file-name' => 'new'], null, $rootHash, [$changedAncestor]);
        $this->assertXmlStringEqualsXmlString(
            '<file xmlns="http://schema.slothsoft.net/savegame/editor" file-name="new"/>',
            $this->serialize($this->createBuilder($changedRoot, $cacheDirectory))
        );
    }
    
    public function changedHashes(): iterable {
        yield 'root changed' => ['file-2', 'archive-1'];
        yield 'ancestor changed' => ['file-1', 'archive-2'];
    }
    
    public function testCacheDirectoryIsRequired(): void {
        $builder = new XmlBuilder(new TestBuildableNode('file'));
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cacheDirectory must be set');
        $this->serialize($builder);
    }
    
    private function createBuilder(BuildableInterface $root, ?string $cacheDirectory = null): XmlBuilder {
        $builder = new XmlBuilder($root);
        $builder->setCacheDirectory($cacheDirectory ?? sys_get_temp_dir());
        return $builder;
    }
    
    private function serialize(XmlBuilder $builder): string {
        return implode('', iterator_to_array($builder->toChunks(), false));
    }
}
