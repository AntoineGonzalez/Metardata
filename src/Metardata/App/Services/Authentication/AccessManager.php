<?php

namespace Metardata\App\Services\Authentication;

class AccessManager
{
    private $authManager;

    function __construct(AuthenticationManager $authManager){
        $this->authManager = $authManager;
    }

    /**
    * The following functions could be merge into a single one function but
    * we'll keep them if we want to split this two functionnalities later.
    **/

    /**
    *
    */
    function hasUpdateAccess() {
        if($this->authManager->isConnected()) return true;
        return false;
    }

    /**
    *
    */
    function hasRemoveAccess() {
        if($this->authManager->isConnected()) return true;
        return false;
    }

    /**
    *
    */
    function hasUploadAccess() {
        if($this->authManager->isConnected()) return true;
        return false;
    }
}
