<?php

namespace Language;

class LanguageFilesApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LanguageFilesApi
     */
    private $api;

    /**
     * @var array
     */
    private $responses = [];

    public function setUp()
    {
        $this->api = new LanguageFilesApi([$this, 'call']);
    }

    public function call()
    {
        return array_shift($this->responses);
    }

    protected function addResponse($data)
    {
        $this->responses[] = [
            'status' => 'OK',
            'data'   => $data,
        ];
    }

    public function testGetLanguageFileSuccess()
    {
        $data = 'Foo bar baz';
        $this->addResponse($data);
        $this->assertEquals($data, $this->api->getLanguageFile('aaa', 'bbb'));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetLanguageFileError()
    {
        $this->api->getLanguageFile('aaa', 'bbb');
    }

    public function testGetAppletLanguagesSuccess()
    {
        $data = ['en'];
        $this->addResponse($data);
        $this->assertEquals($data, $this->api->getAppletLanguages('aaa'));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetAppletLanguagesError()
    {
        $this->api->getAppletLanguages('aaa');
    }

    public function testGetAppletLanguageFileSuccess()
    {
        $data = 'This is test data';
        $this->addResponse($data);
        $this->assertEquals($data, $this->api->getAppletLanguageFile('aaa', 'bbb'));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetAppletLanguageFileError()
    {
        $this->api->getAppletLanguageFile('aaa', 'bbb');
    }

    /**
     * @expectedException \Exception
     */
    public function testApiError1()
    {
        $this->addResponse(false);
        $this->api->getAppletLanguageFile('aaa', 'bbb');
    }

    /**
     * @expectedException \Exception
     */
    public function testApiError2()
    {
        $this->responses[] = ['status' => 'fail'];
        $this->api->getAppletLanguageFile('aaa', 'bbb');
    }
}
