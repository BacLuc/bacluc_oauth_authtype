<?php

namespace Concrete\Package\BaclucOauthAuthType;

defined('C5_EXECUTE') or die(_("Access Denied."));

use Concrete\Core\Package\Package;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class Controller extends Package
{
    protected $pkgHandle               = 'bacluc_oauth_authtype';
    protected $appVersionRequired      = '5.7.4';
    protected $pkgVersion              = '0.0.1';
    protected $pkgAutoloaderRegistries = array(
        'src' => 'BaclucOauthAuthType'
    );

    public function getPackageName()
    {
        return t("BaclucOauthAuthType");
    }

    public function getPackageDescription()
    {
        return t("Adds oauth Authentication Type");
    }

    public function install()
    {
        $em = $this->app->make(EntityManagerInterface::class);
        //begin transaction, so when block install fails, but parent::install was successfully, you don't have to uninstall the package
        $em->getConnection()->beginTransaction();
        try {
            $pkg = parent::install();
            //add blocktypeset

            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }
    }

    public function uninstall()
    {
        $em = $this->app->make(EntityManagerInterface::class);
        $em->getConnection()->beginTransaction();
        try {
            parent::uninstall();
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }
    }

    public function getPackageAutoloaderRegistries()
    {
        return ["src" => "BaclucOauthAuthType"];
    }

}