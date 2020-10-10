<?php

namespace Metardata\App\Services\Authentication;

/**
 *
 */
interface AuthenticationInterface
{
    public function checkAuth($login, $pwd);
    public function synchronize();
    public function isConnected();
    public function getId();
    public function getName();
    public function getFirstname();
    public function getLogin();
    public function getStatut();
    public function logout();

}
