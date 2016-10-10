<?php

namespace App\Bootstrap;

use Core\Bootstrap\BootstrapInterface;
use Core\ControllerDispatcher\Dispatcher;
use Core\ControllerDispatcher\ViewRenderer;
use Core\ControllerDispatcher\RequestMatcher;
use Core\ControllerDispatcher\RouterMapBuilder;
use Core\ParameterWire;
use Core\ViewModel;
use Zend\Diactoros\Uri;
use Zend\Diactoros\ServerRequestFactory;
use Prob\Handler\ParameterMap;
use Prob\Handler\Parameter\Typed;

class DispatcherBootstrap implements BootstrapInterface
{
    private $env = [];

    /**
     * @var ViewModel
     */
    private $viewModel;

    public function __construct()
    {
        $this->viewModel = new ViewModel();
    }

    public function boot(array $env)
    {
        $this->env = $env;

        RequestMatcher::setRequest($this->getServerRequest());
        RequestMatcher::setRouterMap($this->getRouterMap());

        $dispatcher = $this->getDispatcher();
        $viewRenderer = $this->getViewRenderer();

        $viewRenderer->renderView($dispatcher->dispatch());
    }

    private function getDispatcher()
    {
        $dispatcher = new Dispatcher();

        $dispatcher->setRequest($this->getServerRequest());
        $dispatcher->setRouterMap($this->getRouterMap());
        $dispatcher->setParameterMap($this->getParameterMap());

        return $dispatcher;
    }

    private function getViewRenderer()
    {
        $viewRenderer = new ViewRenderer();

        $viewRenderer->setViewEngineConfig($this->env['viewEngine']);
        $viewRenderer->setViewResolver($this->env['viewResolver']);
        $viewRenderer->setViewModel($this->viewModel);

        return $viewRenderer;
    }

    private function getParameterMap()
    {
        $parameterMap = new ParameterMap();
        $parameterMap->bindBy(new Typed(ViewModel::class), $this->viewModel);
        return ParameterWire::injectParameter($parameterMap);
    }

    private function getServerRequest()
    {
        $request = ServerRequestFactory::fromGlobals();

        $stripedUri = new Uri(
            $this->stripAppUrlPrefix($request->getUri()->getPath())
        );

        return $request->withUri($stripedUri);
    }

    private function stripAppUrlPrefix($url)
    {
        $appUrl = $this->env['site']['url'];

        if (substr($url, 0, strlen($appUrl)) === $appUrl) {
            return '/' . substr($url, strlen($appUrl)) ?: '/';
        }

        return $url;
    }

    private function getRouterMap()
    {
        return (new RouterMapBuilder($this->env['router']))->build();
    }
}
