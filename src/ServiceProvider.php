<?php

namespace Concrete\Core\Authentication\Type\ExternalConcrete5;

use OAuth\ServiceFactory;
use OAuth\UserData\ExtractorFactory;

class ServiceProvider extends \Concrete\Core\Foundation\Service\Provider
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
                $factory->registerService('external_concrete5', HitobitoService::class);
                return $factory;
            });
    }
}
