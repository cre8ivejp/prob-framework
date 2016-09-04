<?php

namespace App;

use Core\Application;
use Prob\Rewrite\Request;
use App\Auth\AuthManager;

class Bootstrap
{

    public static function boot()
    {
        self::setUpApplication();
        self::setUpAuthService();

        Application::getInstance()->dispatch(new Request());
    }

    public static function setUpApplication()
    {
        $application = Application::getInstance();

        $application->setSiteConfig(self::getSiteConfig());
        $application->setErrorReporterConfig(self::getErrorReporterConfig());
        $application->setDbConfig(self::getDbConfig());
        $application->setViewEngineConfig(self::getViewEngineConfig());

        $application->setDisplayError(self::getSiteConfig()['displayErrors']);
        $application->registerErrorReporters();

        $application->setEventListener(self::getEventListener());
        $application->registerEventListener();

        $application->setRouterConfig(self::getRouterConfig());
    }

    public static function setUpAuthService()
    {
        $auth = AuthManager::getInstance();

        $auth->setConfig(self::getAuthConfig());
        $auth->setAccountManagerConfig(self::getAccountManagerConfig());
        $auth->setLoginManagerConfig(self::getLoginManagerConfig());
    }

    public static function getSiteConfig()
    {
        return require '../config/site.php';
    }

    public static function getErrorReporterConfig()
    {
        return require '../config/errorReporter.php';
    }

    public static function getDbConfig()
    {
        return require '../config/db.php';
    }

    public static function getViewEngineConfig()
    {
        return require '../config/viewEngine.php';
    }

    public static function getEventListener()
    {
        return require '../config/event.php';
    }

    public static function getRouterConfig()
    {
        return require '../config/router.php';
    }


    public static function getAuthConfig()
    {
        return require 'Auth/config/config.php';
    }

    public static function getAccountManagerConfig()
    {
        return require 'Auth/config/accountManager.php';
    }

    public static function getLoginManagerConfig()
    {
        return require 'Auth/config/loginManager.php';
    }
}
