<?php

namespace Metardata\Framework\Http;

/**
 * Classe qui construit la réponse à la requete utilisateur
 */
class Response
{
  private $headers = array();

  public function addHeader($addedHeader){
    $this->headers[] = $addedHeader;
  }

  public function sendHeaders(){
    foreach($this->headers as $header) {
      header($header);
    }
  }

  public function send($content){
    $this->sendHeaders();
    echo $content;
  }
}

 ?>
