<?php

namespace Language;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Storage
     */
    private $storage;

    public function setUp()
    {
        $this->storage = new Storage('/tmp');
    }

    public function testGetLanguageCacheFile()
    {
        $obj1 = $this->storage->getLanguageCacheFile('foo', 'bar');
        $obj2 = new FileStorage('/tmp/cache/foo/bar.php');
        $this->assertEquals($obj1, $obj2);
    }

    public function testGetAppletLanguageCacheFile()
    {
        $obj1 = $this->storage->getAppletLanguageCacheFile('foo');
        $obj2 = new FileStorage('/tmp/cache/flash/lang_foo.xml');
        $this->assertEquals($obj1, $obj2);
    }
}
