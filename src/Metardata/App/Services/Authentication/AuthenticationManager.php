<?php

namespace Metardata\App\Services\Authentication;

class AuthenticationManager implements AuthenticationInterface
{
    protected $authenticationData;
    protected $request;

    function __construct($request)
    {
        $this->request            = $request;
        $this->authenticationData = $request->getSession("authenticationData");
        if(!isset($this->authenticationData)) {
            $this->authenticationData = array();
        }
    }

    public function checkAuth($login, $pwd){
        $users = array(
            'jml' => array(
                'id' => 12,
                'nom' => 'Lecarpentier',
                'prenom' => 'Jean-Marc',
                'pwd' => 'vivelephp',
                'statut' => 'admin'
            ),
            'alex' => array(
                'id' => 5,
                'nom' => 'Niveau',
                'prenom' => 'Alexandre',
                'pwd' => 'vivelejs',
                'statut' => 'admin'
            )
        );

        if(key_exists($login,$users)){
            $user = $users[$login];
            if($user['pwd'] == $pwd){
                $this->authenticationData['id'] = $user['id'];
                $this->authenticationData['login'] = $login;
                $this->authenticationData['nom'] = $user['nom'];
                $this->authenticationData['prenom'] = $user['prenom'];
                $this->authenticationData['statut'] = $user['statut'];
                $this->synchronize();
            }
        }
    }

    public function isConnected(){
        return !empty($this->authenticationData);
    }

    public function getId(){
        return $this->authenticationData['id'];
    }

    public function getName(){
        return $this->authenticationData['nom'];
    }

    public function getFirstname(){
        return $this->authenticationData['prenom'];
    }

    public function getLogin(){
        return $this->authenticationData['login'];
    }

    public function getStatut(){
        return $this->authenticationData['statut'];
    }

    public function logout(){
        $this->authenticationData = array();
        $this->synchronize();
    }

    public function synchronize(){
        $this->request->setSession('authenticationData', $this->authenticationData);
    }
}
