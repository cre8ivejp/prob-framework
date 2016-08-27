<?php

namespace Core;

use PHPUnit\Framework\TestCase;
use App\ViewEngine\Twig;
use Core\Application;

class TwigViewTest extends TestCase
{
    public function testStringView()
    {
        $viewResolver = new ViewResolver('test');
        $view = $viewResolver->resolve([
            'class' => 'Twig',
            'path' => __DIR__ . '/mock',
            'postfix' => '.twig',
            'settings' => []
        ]);

        $view->set('key', 'ok');

        $this->assertEquals(['key' => 'ok'], $view->getVariables());
        $this->assertEquals('test.twig', $view->getFile());
        $this->expectOutputString('ok');
        $view->render();
    }

    public function testCustomFunctionTest()
    {
        $application = Application::getInstance();
        $application->setSiteConfig([
            'url' => 'http://test.com/',
            'viewEngine' => 'Twig',
        ]);

        $viewResolver = new ViewResolver('testCustomFunction');
        $view = $viewResolver->resolve([
            'class' => 'Twig',
            'path' => __DIR__ . '/mock',
            'postfix' => '.twig',
            'settings' => []
        ]);

        $this->expectOutputString(
            "<link rel=\"stylesheet\" type=\"text/css\" href=\"css_test\">\n" .
            "http://test.com/public/asset_test\n" .
            "http://test.com/url_test"
        );
        $view->render();
    }
}