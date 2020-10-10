<?php

namespace Metardata\App\Controllers;

use Metardata\Framework\Http\Router;
use Metardata\Framework\View\View;
use Metardata\App\Services\ServicesContainer;


class FrontController
{

	protected $request;
	protected $response;
	protected $router;
	protected $services;

	function __construct($request, $response)
	{
		$this->request  = $request;
		$this->response = $response;
		$this->router   = new Router($this->request);
		$this->services = new ServicesContainer($this->request);
	}

	public function execute(){
		$controllerClassName   = $this->router->getClassName();
		$controllerActionName  = $this->router->getActionName();
		$authenticator = $this->services->getAuthenticator();

		if($this->request->getPostParam("message") != null) {
			$postParams = $this->request->getAllPostParam();
			$this->services->getMailer()->sendSupportMessage(
				$postParams["firstname"],
				$postParams["lastname"],
				$postParams["email"],
				$postParams["country"],
				$postParams["message"]
			);
		}

		switch ($controllerClassName) {
			case 'Metardata\App\Controllers\ContactController':
				$view = new View('contact.html.twig');
				break;

			default:
				if($controllerActionName == 'makeForm') {
					$view = new View('metaForm.html.twig');
				} else if($controllerActionName == 'makeDetailPage') {
					$view = new View('details.html.twig');
				} else if($controllerActionName == 'makeLoginPage') {
					$view = new View('login.html.twig');
				} else if($controllerActionName == 'makeAboutPage') {
					$view = new View('about.html.twig');
				} else if($controllerActionName == 'makeShop'){
                    $view = new View('shop.html.twig');
				} else if($controllerActionName == 'shopSuccess'){
                    $view = new View('shopSuccess.html.twig');
				} else if($controllerActionName == 'shopCanceled'){
                    $view = new View('shopCancel.html.twig');
				} else {
					$view = new View('gallery.html.twig');
				}
				break;
		}

		// login
		if($this->request->getPostParam("login") != null && $this->request->getPostParam('pwd') != null) {
			$authenticator->checkAuth($this->request->getPostParam("login"), $this->request->getPostParam('pwd'));
			if(!$authenticator->isConnected()){
				$view->setPart('errorMessage', "Identification incorrecte.");
			}else{
				$view = new View('gallery.html.twig');
				$controllerActionName = "makeGallery";
			}
		}

		if($controllerActionName === "logout") {
			$authenticator->logout();
			$controllerActionName = "defaultAction";
		}

		if($authenticator->isConnected()) {
			$view->setPart('user', $authenticator->getFirstname());
		}

		$controller            = new $controllerClassName(
			$this->request,
			$this->response,
			$this->services,
			$view
		);

		$controller->execute($controllerActionName);

		if ($this->request->isAjaxRequest()) {
			$content = $view->getPart("ajaxContent");
		} else {
			$content = $view->render();
		}

		$this->response->send($content);
	}
}

?>
