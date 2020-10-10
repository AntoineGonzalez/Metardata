<?php

namespace Metardata\App\Services;

use Metardata\App\Models\Picture;

class ShopService{
    protected $shopBag;
    protected $request;

    function __construct($request){
        $this->request = $request;
        $this->shopBag = $this->request->getSession("shopBag");
        if(!isset($this->shopBag)){
            $this->shopBag = array();
        }
    }

    function __destruct(){
        $this->request->setSession("shopBag", $this->shopBag);
    }

    function addPicture($pictureId){
        $this->shopBag[] = $pictureId;
    }

    function removePicture($pictureId){
        unset($this->shopBag[array_search($pictureId,$this->shopBag)]);
    }

    function refresh(){
        $this->shopBag = array();
    }

    function getShopBag(){
        return $this->shopBag;
    }

    function cleanShopBag() {
        $this->shopBag = array();
    }
}

?>
