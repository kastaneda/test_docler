<?php

namespace Language;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class FileStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * set up test environmemt
     */
    public function setUp()
    {
        $this->root = vfsStream::setup('cache');
    }
        
    public function testStore()
    {
        $sampleFile = 'file.php';
        $sampleContent = 'Hello world';
        $storage = new FileStorage(vfsStream::url('cache/' . $sampleFile));
        $this->assertFalse($this->root->hasChild($sampleFile));

        // The file should be created now:
        $storage->store($sampleContent);

        $this->assertTrue($this->root->hasChild($sampleFile));
        $this->assertEquals($this->root->getChild($sampleFile)->getContent(), $sampleContent);
    }

    public function testMakeDir()
    {
        $dir = 'boo';
        $storage = new FileStorage(vfsStream::url('cache/' . $dir . '/file.php'));
        $this->assertFalse($this->root->hasChild($dir));

        // Should create dir
        $storage->store('Sample file content');

        $this->assertTrue($this->root->hasChild($dir));
        $this->assertEquals($this->root->getChild($dir)->getPermissions(), FileStorage::DIR_MODE);
    }

    /**
     * @expectedException \Exception
     */
    public function testError()
    {
        vfsStream::setQuota(10);
        $storage = new FileStorage(vfsStream::url('cache/foo/bar.php'));
        $storage->store('Hello world blah blah blah');
    }
}
