<?php

namespace Metardata\App\Services;

use Metardata\App\Services\MailService;
use Metardata\App\Services\DataExtractService;
use Metardata\App\Services\Authentication\AuthenticationManager;
use Metardata\App\Services\Authentication\AccessManager;
use Metardata\App\Services\ShopService;

class ServicesContainer
{
    protected $extractor;
    protected $mailer;
    protected $authenticationManager;
    protected $shop;
    protected $accessManager;

    public function __construct($request){

        $this->extractor  = new DataExtractService($request);
        $this->mailer     = new MailService();
        $this->authenticationManager = new AuthenticationManager($request);
        $this->shop = new ShopService($request);
        $this->accessManager = new AccessManager($this->authenticationManager);
    }

    public function getExtractor() {
        return $this->extractor;
    }

    public function getMailer() {
        return $this->mailer;
    }

    public function getAuthenticator() {
        return $this->authenticationManager;
    }

    public function getShop() {
        return $this->shop;
    }

    public function getAccessManager() {
        return $this->accessManager;
    }
}
