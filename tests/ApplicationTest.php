<?php

namespace Core;

use PHPUnit\Framework\TestCase;
use Prob\Router\Map;
use Prob\Rewrite\Request;
use App\ViewEngine\StringViewForApplicationTest;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    public function setUp()
    {
        $application = Application::getInstance();
        $application->setSiteConfig($this->getSiteConfig());
        $application->setViewEngineConfig($this->getViewEngineConfig());
        $application->setRouterConfig($this->getRouteMap());

        $this->application = $application;
    }

    private function getSiteConfig()
    {
        return [
            'url' => 'http://test.com/',
            'viewEngine' => 'StringViewForApplicationTest'
        ];
    }

    private function getViewEngineConfig()
    {
        return [
            'StringViewForApplicationTest' => [
                'engine' => 'StringViewForApplicationTest'
            ]
        ];
    }

    private function getRouteMap()
    {
        return [
            'namespace' => 'Core',

            '/string/{board}/{post}' => [
                'GET' => 'TestController.getString',
                'POST' => 'TestController.postString'
            ],

            '/json/{board}/{post}' => [
                'GET' => 'TestController.getJson',
                'POST' => 'TestController.postJson'
            ],

            '/dummy/{board}/{post}' => [
                'GET' => 'TestController.getDummy',
                'POST' => 'TestController.postDummy'
            ]
        ];
    }

    private function setUpRequestAndPathInfo($method, $prefix, $board, $post)
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['PATH_INFO'] = '/'. $prefix . '/' . $board . '/' . $post;
    }

    public function testGetStringDispatcher()
    {
        $method = 'GET';
        $prefix = 'string';
        $board = 'free';
        $post = '5';

        $view = new StringViewForApplicationTest();
        $controller = new TestController();
        $viewModel = new ViewModel();

        $returnOfController = $controller->getString($board, $post, $viewModel);
        $view->file($returnOfController);
        $view->set('key', $viewModel->getVariables()['key']);

        $this->expectOutputString($view->getRenderingResult());

        $this->setUpRequestAndPathInfo($method, $prefix, $board, $post);
        $this->application->dispatcher(new Request());
    }

    public function testPostStringDispatcher()
    {
        $method = 'POST';
        $prefix = 'string';
        $board = 'free';
        $post = '5';

        $view = new StringViewForApplicationTest();
        $controller = new TestController();
        $viewModel = new ViewModel();

        $returnOfController = $controller->postString($board, $post, $viewModel);
        $view->file($returnOfController);
        $view->set('key', $viewModel->getVariables()['key']);

        $this->expectOutputString($view->getRenderingResult());

        $this->setUpRequestAndPathInfo($method, $prefix, $board, $post);
        $this->application->dispatcher(new Request());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetJsonDispatcher()
    {
        $method = 'GET';
        $prefix = 'json';
        $board = 'free';
        $post = '5';

        $controller = new TestController();

        $jsonResult = json_encode($controller->getJson($board, $post));
        $this->expectOutputString($jsonResult);

        $this->setUpRequestAndPathInfo($method, $prefix, $board, $post);
        $this->application->dispatcher(new Request());
    }

    /**
     * @runInSeparateProcess
     */
    public function testPostJsonDispatcher()
    {
        $method = 'POST';
        $prefix = 'json';
        $board = 'free';
        $post = '5';

        $controller = new TestController();

        $jsonResult = json_encode($controller->postJson($board, $post));
        $this->expectOutputString($jsonResult);

        $this->setUpRequestAndPathInfo($method, $prefix, $board, $post);
        $this->application->dispatcher(new Request());
    }

    public function testGetDummyDispatcher()
    {
        $method = 'GET';
        $prefix = 'dummy';
        $board = 'free';
        $post = '5';

        $controller = new TestController();

        $this->expectOutputString($controller->generateViewModelKeyValue($method, $board, $post));

        $this->setUpRequestAndPathInfo($method, $prefix, $board, $post);
        $this->application->dispatcher(new Request());
    }

    public function testPostDummyDispatcher()
    {
        $method = 'POST';
        $prefix = 'dummy';
        $board = 'free';
        $post = '5';

        $controller = new TestController();

        $this->expectOutputString($controller->generateViewModelKeyValue($method, $board, $post));

        $this->setUpRequestAndPathInfo($method, $prefix, $board, $post);
        $this->application->dispatcher(new Request());
    }

    public function testUrl()
    {
        $siteUrl = $this->getSiteConfig()['url'];
        $url = '/test/ok';

        $expectUrl = $siteUrl.$url;

        $this->assertEquals($expectUrl, $this->application->url($url));
    }
}

class TestController
{
    public static function generateViewModelKeyValue($method, $board, $post)
    {
        return $method . ': /' . $board . '/' . $post;
    }

    public static function generateJsonArray($method, $board, $post)
    {
        return [ $method, $board, $post ];
    }


    public function getString($board, $post, ViewModel $model)
    {
        $model->set('key', $this->generateViewModelKeyValue('GET', $board, $post));
        return 'test/get';
    }

    public function postString($board, $post, ViewModel $model)
    {
        $model->set('key', $this->generateViewModelKeyValue('POST', $board, $post));
        return 'test/post';
    }

    public function getJson($board, $post)
    {
        return $this->generateJsonArray('GET', $board, $post);
    }

    public function postJson($board, $post)
    {
        return $this->generateJsonArray('POST', $board, $post);
    }

    public function getDummy($board, $post)
    {
        echo $this->generateViewModelKeyValue('GET', $board, $post);
    }

    public function postDummy($board, $post)
    {
        echo $this->generateViewModelKeyValue('POST', $board, $post);
    }
}

namespace App\ViewEngine;

class StringViewForApplicationTest extends DummyView
{
    private $templateFilename = '';
    private $var = [];

    public function set($key, $value)
    {
        $this->var[$key] = $value;
    }

    public function file($fileName)
    {
        $this->templateFilename = $fileName;
    }

    public function getRenderingResult()
    {
        return $this->templateFilename . ' -- ' . $this->var['key'];
    }

    public function render()
    {
        echo $this->getRenderingResult();
    }
}
