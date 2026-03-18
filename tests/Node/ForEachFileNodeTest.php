<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use PHPUnit\Framework\TestCase;
use Slothsoft\FarahTesting\TestUtils;
use Slothsoft\Savegame\Editor;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Savegame\EditorConfig;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Savegame\Node\ArchiveParser\TestArchiveExtractor;
use PHPUnit\Framework\Constraint\IsEqual;

/**
 * ForEachFileNodeTest
 *
 * @see ForEachFileNode
 */
final class ForEachFileNodeTest extends TestCase {
    
    protected function setUp(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ForEachFileNode::class), "Failed to load class 'Slothsoft\Savegame\Node\ForEachFileNode'!");
    }
    
    private static function getEditor(string $xml): Editor {
        $dom = new DOMHelper();
        $document = $dom->parseDocument($xml);
        $infosetFile = FileInfoFactory::createFromDocument($document);
        
        $archiveExtractors = [];
        $archiveExtractors['test'] = new TestArchiveExtractor();
        $archiveBuilders = [];
        
        $config = new EditorConfig(FileInfoFactory::createFromPath('test-files'), FileInfoFactory::createFromPath(ServerEnvironment::getDataDirectory()), FileInfoFactory::createFromPath(ServerEnvironment::getCacheDirectory()), $infosetFile, $archiveExtractors, $archiveBuilders);
        return new Editor($config);
    }
    
    public function testList() {
        $xml = <<<EOT
<savegame xmlns="http://schema.slothsoft.net/savegame/editor" version="0.4">
    <archive path="archive.txt" type="test">
    	<for-each-file list="001 003 015"/>
    </archive>
</savegame>
EOT;
        $editor = self::getEditor($xml);
        $archive = $editor->loadArchive('archive.txt', true);
        
        $actual = [];
        /** @var FileContainer $file */
        foreach ($archive->getFileNodes() as $file) {
            $actual[] = $file->getFileName();
        }
        
        $this->assertThat($actual, new IsEqual([
            '001',
            '003',
            '015'
        ]));
    }
    
    public function testRangeStart() {
        $xml = <<<EOT
<savegame xmlns="http://schema.slothsoft.net/savegame/editor" version="0.4">
    <archive path="archive.txt" type="test">
    	<for-each-file range-start="010"/>
    </archive>
</savegame>
EOT;
        $editor = self::getEditor($xml);
        $archive = $editor->loadArchive('archive.txt', true);
        
        $actual = [];
        /** @var FileContainer $file */
        foreach ($archive->getFileNodes() as $file) {
            $actual[] = $file->getFileName();
        }
        
        $this->assertThat($actual, new IsEqual([
            '010',
            '011',
            '012',
            '013',
            '014',
            '015'
        ]));
    }
    
    public function testRangeEnd() {
        $xml = <<<EOT
<savegame xmlns="http://schema.slothsoft.net/savegame/editor" version="0.4">
    <archive path="archive.txt" type="test">
    	<for-each-file range-end="002"/>
    </archive>
</savegame>
EOT;
        $editor = self::getEditor($xml);
        $archive = $editor->loadArchive('archive.txt', true);
        
        $actual = [];
        /** @var FileContainer $file */
        foreach ($archive->getFileNodes() as $file) {
            $actual[] = $file->getFileName();
        }
        
        $this->assertThat($actual, new IsEqual([
            '001',
            '002'
        ]));
    }
    
    public function testRange() {
        $xml = <<<EOT
<savegame xmlns="http://schema.slothsoft.net/savegame/editor" version="0.4">
    <archive path="archive.txt" type="test">
    	<for-each-file range-start="009" range-end="011"/>
    </archive>
</savegame>
EOT;
        $editor = self::getEditor($xml);
        $archive = $editor->loadArchive('archive.txt', true);
        
        $actual = [];
        /** @var FileContainer $file */
        foreach ($archive->getFileNodes() as $file) {
            $actual[] = $file->getFileName();
        }
        
        $this->assertThat($actual, new IsEqual([
            '009',
            '010',
            '011'
        ]));
    }
}