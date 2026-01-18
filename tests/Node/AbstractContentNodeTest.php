<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame\Node;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\FileSystem;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\FarahTesting\TestUtils;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorConfig;
use Slothsoft\Savegame\Node\ArchiveParser\CopyArchiveBuilder;
use Slothsoft\Savegame\Node\ArchiveParser\CopyArchiveExtractor;
use PHPUnit\Framework\Constraint\IsEqual;

/**
 * AbstractContentNodeTest
 *
 * @see AbstractContentNode
 */
final class AbstractContentNodeTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractContentNode::class), "Failed to load class 'Slothsoft\Savegame\Node\AbstractContentNode'!");
    }
    
    protected function setUp(): void {
        parent::setUp();
        TestUtils::changeWorkingDirectoryToComposerRoot();
    }
    
    private function createSavegame(string $xml): SavegameNode {
        $dataDirectory = FileInfoFactory::createFromPath('test-files');
        
        $file = FileInfoFactory::createFromString($xml);
        
        $cacheDirectory = temp_dir(__CLASS__);
        FileSystem::ensureDirectory($cacheDirectory);
        $cacheDirectory = FileInfoFactory::createFromPath($cacheDirectory);
        
        $extractors = [
            'COPY' => new CopyArchiveExtractor()
        ];
        
        $builders = [
            'COPY' => new CopyArchiveBuilder()
        ];
        
        $config = new EditorConfig($dataDirectory, $cacheDirectory, $cacheDirectory, $file, $extractors, $builders);
        $editor = new Editor($config);
        
        return $editor->getSavegameNode();
    }
    
    /**
     *
     * @dataProvider positionAtStringProvider
     */
    public function test_position(string $file, string $search, int $position): void {
        $size = strlen($search);
        
        $xml = <<<EOT
<savegame version="0.4" xmlns="http://schema.slothsoft.net/savegame/editor">
    <archive path="$file" type="COPY">
        <for-each-file>
            <string name="search" position="$position" size="$size" />
        </for-each-file>
    </archive>
</savegame>
EOT;
        $savegame = $this->createSavegame($xml);
        
        $archive = $savegame->getArchiveById($file);
        $archive->load(true);
        
        $file = $archive->getFileNodeByName('1');
        $this->assertThat($file->findStringAtOrAfter($search), new IsEqual($position));
        
        $value = $file->getValueByName('search');
        
        $this->assertThat($value->getContentOffset(), new IsEqual($position));
        $this->assertThat($value->getValue(), new IsEqual($search));
    }
    
    /**
     *
     * @dataProvider positionAtStringProvider
     */
    public function test_positionAtString(string $file, string $search, int $position): void {
        $size = strlen($search);
        
        $xml = <<<EOT
<savegame version="0.4" xmlns="http://schema.slothsoft.net/savegame/editor">
    <archive path="$file" type="COPY">
        <for-each-file>
            <string name="search" position-at-string="$search" size="$size" />
        </for-each-file>
    </archive>
</savegame>
EOT;
        $savegame = $this->createSavegame($xml);
        
        $archive = $savegame->getArchiveById($file);
        $archive->load(true);
        
        $file = $archive->getFileNodeByName('1');
        $value = $file->getValueByName('search');
        
        $this->assertThat($value->getContentOffset(), new IsEqual($position));
        $this->assertThat($value->getValue(), new IsEqual($search));
    }
    
    public function positionAtStringProvider(): iterable {
        yield 'AM2 Server 105' => [
            'DATA-105',
            'AM2 - Copper server',
            0x478A
        ];
        
        yield 'AM2 Server 101' => [
            'DATA-101',
            'AM2 - Copper server',
            0x4796
        ];
    }
}