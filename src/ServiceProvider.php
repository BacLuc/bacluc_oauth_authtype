<?php

namespace BaclucOauthAuthtype;

use Concrete\Core\Foundation\Service\Provider;
use Concrete\Package\BaclucOauthAuthtype\Authentication\BaclucOauthHitobito\Controller;
use OAuth\ServiceFactory;
use OAuth\UserData\ExtractorFactory;

class ServiceProvider extends Provider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        // Register our extractor
        $this->app->extend('oauth/factory/extractor',
            function (ExtractorFactory $factory) {
                $factory->addExtractorMapping(HitobitoService::class, Extractor::class);

                return $factory;
            });

        // Register our service
        $this->app->extend('oauth/factory/service',
            function (ServiceFactory $factory) {
                $factory->registerService(Controller::OAUTH_HANDLE, HitobitoService::class);
                return $factory;
            });
    }
}
