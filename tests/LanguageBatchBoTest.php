<?php

namespace Language;

class LanguageBatchBoTest extends \PHPUnit_Framework_TestCase
{
    public function testSomething()
    {
        $filesApi = $this->createMock(LanguageFilesApi::class);
        $storage = $this->createMock(Storage::class);
        $fileStorage = $this->createMock(FileStorage::class);
        $applications = ['app01' => ['en']];
        $applets = ['app02' => 'SomeAppletName'];

        $filesApi->method('getLanguageFile')->willReturn('sample content');
        $filesApi->method('getAppletLanguages')->willReturn(['fr']);
        $filesApi->method('getAppletLanguageFile')->willReturn('<xml />');
        $storage->method('getLanguageCacheFile')->willReturn($fileStorage);
        $storage->method('getAppletLanguageCacheFile')->willReturn($fileStorage);
        $fileStorage->method('store')->willReturn(true);

        $bo = new LanguageBatchBo($filesApi, $storage, $applications, $applets);

        ob_start();
        $bo->generateLanguageFiles();
        $this->assertContains('app01', ob_get_contents());
        ob_end_clean();

        ob_start();
        $bo->generateAppletLanguageXmlFiles();
        $this->assertContains('[fr]', ob_get_contents());
        ob_end_clean();
    }

    /**
     * @expectedException \Exception
     */
    public function testError()
    {
        $filesApi = $this->createMock(LanguageFilesApi::class);
        $storage = $this->createMock(Storage::class);
        $fileStorage = $this->createMock(FileStorage::class);
        $applications = ['app01' => ['en']];
        $applets = ['app02' => 'SomeAppletName'];

        $bo = new LanguageBatchBo($filesApi, $storage, $applications, $applets);

        $filesApi->method('getAppletLanguages')->willReturn([]);
        $this->expectOutputRegex('/SomeAppletName/');
        $bo->generateAppletLanguageXmlFiles();
    }
}
