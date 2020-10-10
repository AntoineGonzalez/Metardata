<?php

namespace Metardata\Framework\Http;

/**
*
*/
class Router
{
    protected $request;
    protected $controllerClassName;
    protected $controllerAction;

    function __construct(Request $request)
    {
        $this->request = $request;
        $this->parseRequest();
    }

    public function parseRequest()
    {
        $objet = $this->request->getGetParam('a');
        switch($objet) {
            case 'makeContactPage' :
                $this->controllerClassName = 'Metardata\App\Controllers\ContactController';
                break;
            default : $this->controllerClassName = 'Metardata\App\Controllers\PictureGalleryController';
        }

        if(!class_exists($this->controllerClassName)) {
            throw new Exception('la classe {$this->controllerClassName} n\'éxiste pas');
        }

        $this->controllerAction = $this->request->getGetParam('a','defaultAction');
    }

    public function getClassName()
    {
        return $this->controllerClassName;
    }

    public function getActionName()
    {
        return $this->controllerAction;
    }
}
