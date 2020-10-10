<?php

namespace Metardata\Framework\Http;

/**
 * Classe qui analyse la requete utlisateur (HTTP / url * / param POST GET FILE SERVER)
 */
class Request
{

  private $get;
  private $post;
  private $server;
  private $files;
  private $session;

  public function __construct($get, $post, $server, $files, $session)
  {
    $this->get = $get;
    $this->post = $post;
    $this->server = $server;
    $this->files = $files;
    $this->session = $session;
  }

  public function __destruct() {
      $_SESSION = $this->session;
  }

  public function getGetParam($key, $default = null)
  {
    if(!isset($this->get[$key])){
      return $default;
    }
    return $this->get[$key];
  }

  public function getPostParam($key, $default = null)
  {
    if(!isset($this->post[$key])){
      return $default;
    }
    return $this->post[$key];
  }

  public function getFileParam($key, $default = null)
  {
    if(!isset($this->files[$key])){
      return $default;
    }
    return $this->files[$key];
  }

  public function getAllPostParam()
  {
    return $this->post;
  }

  public function getAllFileParam()
  {
    return $this->files;
  }

  public function isAjaxRequest()
  {
      return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
      (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
  }

  public function getSession($key, $default = null)
  {
      if(!isset($this->session[$key])){
          return $default;
      }
      return $this->session[$key];
  }

  public function setSession($key, $value)
  {
    $this->session[$key] = $value;
  }

}


?>
