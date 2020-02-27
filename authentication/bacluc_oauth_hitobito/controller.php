<?php

namespace Concrete\Package\BaclucOauthAuthtype\Authentication\BaclucOauthHitobito;

use BaclucOauthAuthtype\ServiceFactory;
use Concrete\Core\Authentication\Type\OAuth\OAuth2\GenericOauth2TypeController;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Core\User\Group\GroupList;
use Concrete\Core\User\User;
use InvalidArgumentException;
use League\Url\Url;

class Controller extends GenericOauth2TypeController
{
    const OAUTH_HANDLE = 'bacluc_oauth_hitobito';

    /** @var \Concrete\Core\Authentication\Type\ExternalConcrete5\ServiceFactory */
    protected $factory;

    /** @var \Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface */
    protected $urlResolver;

    /** @var \Concrete\Core\Config\Repository\Repository */
    protected $config;

    public function __construct(
        \Concrete\Core\Authentication\AuthenticationType $type = null,
        ServiceFactory $factory,
        ResolverManagerInterface $urlResolver,
        Repository $config
    ) {
        parent::__construct($type);
        $this->factory = $factory;
        $this->urlResolver = $urlResolver;
        $this->config = $config;
    }

    /**
     * Get the ID of the group to enter upon registration
     * This method grabs the registration group ID out of the auth config group
     *
     * @return int
     */
    public function registrationGroupID()
    {
        return (int)$this->config->get('auth.' . self::OAUTH_HANDLE . '.registration.group');
    }

    /**
     * Determine whether this type supports automatically registering users
     * This method grabs this configuration from the auth config group
     *
     * @return bool
     */
    public function supportsRegistration()
    {
        return (bool)$this->config->get('auth.' . self::OAUTH_HANDLE . '.registration.enabled', false);
    }

    /**
     * Build and return this authentication type's icon HTML
     *
     * @return string
     */
    public function getAuthenticationTypeIconHTML()
    {
        $svgData = file_get_contents(DIR_BASE_CORE . '/images/authentication/community/concrete.svg');
        $publicSrc = '/concrete/images/authentication/community/concrete.svg';

        return "<div class='ccm-concrete-authentication-type-svg' data-src='{$publicSrc}'>{$svgData}</div>";
    }

    public function getHandle()
    {
        return self::OAUTH_HANDLE;
    }

    /**
     * Get the service object associated with this authentication type
     * This method uses the oauth/factory/service object to create our service if one is not set
     *
     * @return \OAuth\Common\Service\ServiceInterface|\OAuth\OAuth2\Service\AbstractService
     */
    public function getService()
    {
        if (!$this->service) {
            /** @var \OAuth\ServiceFactory $serviceFactory */
            $serviceFactory = $this->app->make('oauth/factory/service');
            $this->service = $this->factory->createService($serviceFactory);
        }

        return $this->service;
    }

    /**
     * Save data for this authentication type
     * This method is called when the type_form.php submits. It stores client details and configuration for connecting
     *
     * @param array|\Traversable $args
     */
    public function saveAuthenticationType($args)
    {
        $passedUrl = trim($args['url']);

        if ($passedUrl) {
            try {
                $url = Url::createFromUrl($passedUrl);

                if (!(string)$url->getScheme() || !(string)$url->getHost()) {
                    throw new InvalidArgumentException('No scheme or host provided.');
                }

            } catch (\Exception $e) {
                throw new InvalidArgumentException('Invalid URL.');
            }
        }

        $passedName = trim($args['displayName']);
        if (!$passedName) {
            throw new InvalidArgumentException('Invalid display name');
        }
        $this->authenticationType->setAuthenticationTypeName($passedName);

        $config = $this->app->make(Repository::class);
        $config->save('auth.' . self::OAUTH_HANDLE . '.url', $args['url']);
        $config->save('auth.' . self::OAUTH_HANDLE . '.appid', $args['apikey']);
        $config->save('auth.' . self::OAUTH_HANDLE . '.secret', $args['apisecret']);
        $config->save('auth.' . self::OAUTH_HANDLE . '.registration.enabled', (bool)$args['registration_enabled']);
        $config->save('auth.' . self::OAUTH_HANDLE . '.registration.group', intval($args['registration_group'], 10));
    }

    /**
     * Controller method for type_form
     * This method is called just before rendering type_form.php, use it to set data for that template
     */
    public function edit()
    {
        $config = $this->app->make(Repository::class);
        $this->set('form', $this->app->make('helper/form'));
        $this->set('data', $config->get('auth.' . self::OAUTH_HANDLE, []));
        $this->set('redirectUri',
            $this->urlResolver->resolve(['/ccm/system/authentication/oauth2/' . self::OAUTH_HANDLE . '/callback']));

        $list = $this->app->make(GroupList::class);
        $this->set('groups', $list->getResults());
    }

    /**
     * Controller method for form
     * This method is called just before form.php is rendered, use it to set data for that template
     */
    public function form()
    {
        $this->setData();
    }

    /**
     * Controller method for the hook template
     * This method is called before hook.php is rendered, use it to set data for that template
     */
    public function hook()
    {
        $this->setData();
    }

    /**
     * Controller method for the hooked template
     * This method gets called before hooked.php is rendered, use it to set data for that template
     */
    public function hooked()
    {
        $this->setData();
    }

    /**
     * Method for setting general data for all views
     */
    private function setData()
    {
        $data = $this->config->get('auth.' . self::OAUTH_HANDLE, '');
        $authUrl =
            $this->urlResolver->resolve(['/ccm/system/authentication/oauth2/' . self::OAUTH_HANDLE . '/attempt_auth']);
        $attachUrl =
            $this->urlResolver->resolve([
                '/ccm/system/authentication/oauth2/' .
                self::OAUTH_HANDLE .
                '/attempt_attach'
            ]);
        $baseUrl = $this->urlResolver->resolve(['/']);
        $path = $baseUrl->getPath();
        $path->remove('index.php');
        $name = trim((string)array_get($data, 'name', t('External oauth')));

        $this->set('data', $data);
        $this->set('authUrl', $authUrl);
        $this->set('attachUrl', $attachUrl);
        $this->set('baseUrl', $baseUrl);
        $this->set('assetBase', $baseUrl->setPath($path));
        $this->set('name', $name);
        $this->set('user', $this->app->make(User::class));
    }
}
