<?php

namespace Metardata\App\Controllers;

use Metardata\Framework\Http\Request;
use Metardata\Framework\Http\Response;
use Metardata\Framework\View\View;
use Metardata\App\Services\ServicesContainer;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

class ContactController
{
    protected $request;
    protected $response;
    protected $view;

    public function __construct(Request $request,
                                Response $response,
                                ServicesContainer $services,
                                View  $view){

        $this->request  = $request;
        $this->response = $response;
        $this->services = $services;
        $this->view     = $view;
        $this->view->setPart('title', 'yo');
  }

  public function execute($action){
    $this->$action();
  }

  public function defaultAction(){
    $this->makeContactPage();
  }

  public function makeContactPage(){
    $title = 'Contact us';
    $content = 'Le contenu';

    $this->view->setPart('title', $title);
    $this->view->setPart('content',$content);
  }

}

?>
