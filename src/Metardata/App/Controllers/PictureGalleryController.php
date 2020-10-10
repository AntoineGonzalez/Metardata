<?php

namespace Metardata\App\Controllers;

use Metardata\Framework\Http\Request;
use Metardata\Framework\Http\Response;
use Metardata\Framework\View\View;
use Metardata\App\Services\ServicesContainer;
use Metardata\App\Models\Picture;

class PictureGalleryController
{
    protected $request;
    protected $response;
    protected $view;
    protected $services;
    protected $imgDirectory;
    protected $allPictures;
    protected $totalPageNumber;
    protected $current;
    protected $numberToDisplay;

    public function __construct(Request $request,
                                Response $response,
                                ServicesContainer $services,
                                View  $view){

        $this->request  = $request;
        $this->response = $response;
        $this->services = $services;
        $this->view     = $view;
        $this->imgDirectory = 'public/images/';
        $this->allPictures = scandir($this->imgDirectory);
        $this->allPictures = array_diff($this->allPictures, array('.', '..'));
        natsort($this->allPictures);
        $this->allPictures = array_values($this->allPictures);
        for($i = 0 ; $i<sizeof($this->allPictures); $i++) {
            $path = $this->allPictures[$i];
            $this->allPictures[$i] = new Picture($this->imgDirectory.$path);
        }
        $this->totalPageNumber  = intval(sizeof($this->allPictures) / 9) + 1;
        $this->numberToDisplay = 10;
  }

    public function execute($action){
        $this->$action();
    }

    public function defaultAction(){
        $this->makeGallery();
    }

    public function makeGallery(){
        $title      = 'Gallery';
        //building pagination
        $pagination = array();
        for ($i = 1; $i <= $this->totalPageNumber; $i++) {
            $pagination[$i] = $i;
        }

        //building pictures
        $picturesPart = array_slice($this->allPictures, 0, $this->numberToDisplay);
        $this->view->setPart('title', $title);
        $this->view->setPart('pictures', $picturesPart);
        $this->view->setPart('pagination', $pagination);
        $this->request->setSession("currentPageSession", 1);
    }

    public function makeForm() {
        $title = 'Exiftool form';
        $uploadAccess = $this->services->getAccessManager()->hasUploadAccess();
        $this->view->setPart('uploadAccess', $uploadAccess);
        $this->view->setPart('title', $title);
        $this->view->setPart('hasUploadAccess', $title);
    }

    public function getIptcForPicture(){
        $pictureId = $this->request->getGetParam("pictureId");
        $picture = $this->allPictures[$pictureId - 1];
        $this->services->getExtractor()->extract($picture, true);
        $response = new \stdClass();
        $response->status = 200;
        $metadata = $picture->getMeta();
        if($metadata && key_exists("IPTC",$metadata)){
            $response->iptc = $metadata["IPTC"];
        }
        $this->view->setPart("ajaxContent",json_encode($response));
    }

    public function makeDetailPage() {
      $canUpdate = $this->services->getAccessManager()->hasUpdateAccess();
      $canRemove = $this->services->getAccessManager()->hasRemoveAccess();
      $pictureId = $this->request->getGetParam("pictureId");
      $title      = 'Detail';
      $picture = $this->allPictures[$pictureId - 1];
      $this->services->getExtractor()->extract($picture);
      $metaIptc = $picture->getMeta();

      if($metaIptc && key_exists("IPTC", $metaIptc)) $metaIptc = $metaIptc["IPTC"];

      // view parts
      $this->view->setPart('picture', $picture);
      $this->view->setPart('data',$metaIptc);
      $latitude = $picture->getLatitude();
      $longitude = $picture->getLongitude();
      if($latitude && $longitude) {
          $this->view->setPart('long', $picture->getLongitude());
          $this->view->setPart('lat',  $picture->getLatitude());
      }

      $this->view->setPart('canUpdate', $canUpdate);
      $this->view->setPart('canRemove', $canRemove);
    }

    public function deletePicture(){
      $id = $this->request->getGetParam("pictureId");
      unlink($this->allPictures[$id-1]->getPath());
      unset($this->allPictures[$id-1]);
      foreach ($this->allPictures as $picture) {
          if($picture->getId() > $id){
              $oldPath = $picture->getPath();
              $ext = explode(".",$oldPath)[1];
              $oldId = $picture->getId();
              $picture->setId($oldId-1);
              rename($oldPath, PICTURE_PATH."picture".$picture->getId().".".$ext);
          }
      }
      $response = new \stdClass();
      $response->status = 200;
      $this->view->setPart("ajaxContent",json_encode($response));
    }

    public function makeLoginPage() {

    }

    public function makeAboutPage() {

    }

    public function getNextPictures() {
      $currentPage = $this->request->getSession("currentPageSession");
      $nextStartIndex = ($currentPage - 1) * $this->numberToDisplay + $this->numberToDisplay;

      // init response object
      $response = new \stdClass();

      if($nextStartIndex <= sizeof($this->allPictures)) {
          $response->code = 200;
          $response->page = $currentPage + 1;
          $response->totalPageNumber = $this->totalPageNumber;
          $response->startIndex = $nextStartIndex;

          $response->pictures =  $this->getPicturePart($response->startIndex);

          $this->request->setSession("currentPageSession", $currentPage + 1);
      } else {
          // out of bounds
          $response->code = 404;
          $response->message = 'Error: Pictures out of range';
      }
      $this->view->setPart('ajaxContent', json_encode($response));
    }

    public function getPreviousPictures() {
      $currentPage = $this->request->getSession("currentPageSession");
      $previousStartIndex = ($currentPage - 1)  * $this->numberToDisplay - $this->numberToDisplay;

      // init response object
      $response = new \stdClass();

      if($previousStartIndex >= 0) {
          $response->code = 200;
          $response->page = $currentPage - 1;
          $response->totalPageNumber = $this->totalPageNumber;
          $response->startIndex = $previousStartIndex;

          $response->pictures = $this->getPicturePart($response->startIndex);

          $this->request->setSession("currentPageSession", $currentPage - 1);
      } else {
          // out of bounds
          $response->code = 404;
          $response->message = 'Error: Pictures out of range';
      }
      $this->view->setPart('ajaxContent', json_encode($response));
    }

    public function getPicturePage() {
      $index = $this->request->getGetParam("index");

      // init response object
      $response = new \stdClass();

      if(!$index) {
          $response->code = 404;
          $response->message = 'Error: Invalid index';
      } else {
          $response->code = 200;
          $response->page = $index;
          $response->totalPageNumber = $this->totalPageNumber;
          $response->startIndex = ($index - 1) * $this->numberToDisplay;

          $response->pictures =  $this->getPicturePart($response->startIndex);
          $this->request->setSession("currentPageSession", $index);
      }
      $this->view->setPart('ajaxContent', json_encode($response));
    }

    public function getPicturePart($startIndex) {
      // building picture part
      $picturesPart = array_slice($this->allPictures, $startIndex, $this->numberToDisplay);
      $pictures = array();

      foreach ($picturesPart as $key => $value) {
          $picture = new \stdClass();
          $picture->jsonPath = $value->getPath();
          $picture->jsonMeta = $value->getMeta();
          $picture->jsonId = $value->getId();
          array_push($pictures, $picture);
      }

      return $pictures;
    }

    public function uploadPicture() {
    $files = $this->request->getFileParam("attachment");
    $target_dir = "public/images/";

    // init response
    $response = new \stdClass();
    $response->status = 200;
    $response->files = $files;
    $response->picturesAdded = [];

    for($i = 0; $i < sizeof($files["name"]); $i++) {
        $path = pathinfo($files["name"][$i]);
        $filename = $path['filename'];
        $ext = $path['extension'];
        $temp_name = $files['tmp_name'][$i];
        $pictureIndex = sizeof($this->allPictures) + 1 + $i;
        $path_filename_ext = "picture".$pictureIndex.".".$ext;
        $res = move_uploaded_file($temp_name, $target_dir.$path_filename_ext);
        if(!$res) {
            $response->status = 500;
        } else {
            //extract data
            $picture = new Picture($target_dir.$path_filename_ext);
            $this->services->getExtractor()->extract($picture);

            $added = new \stdClass();
            $added->filename = $filename;
            $added->data = $picture->getMeta();
            $response->picturesAdded[$i] = $added;
        }
    }
    $this->view->setPart('ajaxContent', json_encode($response));
    }

    public function saveMetadata() {
      $response = new \stdClass();
      $response->status = 200;
      $data = $this->request->getPostParam("data");
      for($i = 0; $i < sizeof($data); $i++) {
          $currentPic = $data[$i];
          $picture = new Picture($currentPic["path"]);
          $updated = $this->services->getExtractor()->updateMeta($picture, $currentPic);
          if(!$updated) {
               $response->status = 500;
         }
      }
      $this->view->setPart('ajaxContent', json_encode($response));
    }

    public function makeShop(){
    $title = 'Panier';
    $shopBag = $this->services->getShop()->getShopBag();
    $pictures = array();

    foreach ($shopBag as $pictureId) {
        $picture = $this->allPictures[$pictureId-1];
        $pictures[] = $picture;
    }

    $totalMessage = sizeof($shopBag) * 2.50;

    $this->view->setPart('pictures', $pictures);
    $this->view->setPart('total', $totalMessage);
    }

    public function getPaiementItems() {
        $mail = $this->request->getGetParam("mail");
        $firstname = $this->request->getGetParam("firstname");

        $shopBag = $this->services->getShop()->getShopBag();
        $total = sizeof($shopBag) * 250;
        $numClient = rand(1,1000);

        $params = array(
          "amount" => $total,
          "merchant_id" => "014295303911111",
          "merchant_country" => "fr",
          "currency_code" => 978,
          "pathfile" => LCL_PATHFILE,
          "transaction_id" => "",
          "normal_return_url" => "https://dev-21500894.users.info.unicaen.fr/devoir-idc2019/shop_success",
          "cancel_return_url" => "https://dev-21500894.users.info.unicaen.fr/devoir-idc2019/shop_cancel",
          "automatic_response_url" => "https://dev-21500894.users.info.unicaen.fr/devoir-idc2019/gallery",
          "language" => "fr",
          "payment_means" => "CB,2,VISA,2,MASTERCARD,2",
          "header_flag" => "no",
          "capture_day" => "",
          "capture_mode" => "",
          "background_id" => "",
          "bgcolor" => "",
          "block_align" => "middle",
          "block_order" => "",
          "textcolor" => "",
          "textfont" => "",
          "templatefile" => "",
          "logo_id" => "",
          "receipt_complement" => "",
          "caddie" => implode(",", $shopBag),
          "customer_id" => $numClient,
          "customer_email" => "",
          "customer_ip_address" => "",
          "data" => "$mail,$firstname",
          "return_context" => "",
          "target" => "",
          "order_id" => "10",
        );

        $params_string = "";
        foreach ($params as $c => $v) {
            $params_string .= $c . "=" . $v ." ";
        }

        $request = exec(LCL_REQUEST." $params_string");

        $request = explode('!', $request);

        $code = $request[1];
        $message = $request[3];

        $response = new \stdClass();

        if(!$message || $code != 0) {
            $response->status = 500;
        } else {
            $response->status = 200;
            $response->message = $message;
        }

        $this->view->setPart("ajaxContent", json_encode($response));
    }

    public function shopSuccess() {
      $message_data = $this->request->getPostParam('DATA');

      $response =  exec(LCL_RESPONSE." message=$message_data pathfile=".LCL_PATHFILE);

      $tableau = explode('!', $response);

      $code = $tableau[1];
      $caddie = $tableau[22];
      $data = $tableau[32];

      $data = explode(",", $data);
      $firstname = $data[1];
      $mail = $data[0];

      if($code == 0) {
          $caddie = explode(",", $caddie);
          $pictures = array();
          foreach ($caddie as $pictureId) {
              $picture = $this->allPictures[$pictureId-1];
              $pictures[] = $picture;
          }
          $this->services->getMailer()->sendPictures($firstname, $mail, $pictures);
          $this->services->getShop()->cleanShopBag();
      }

      $this->view->setPart("responseCode", $code);
      $this->view->setPart("mail", $mail);
    }

    public function shopCanceled() {
    $message_data = $_POST['DATA'];

    $response =  exec(LCL_RESPONSE." message=$message_data pathfile=".LCL_PATHFILE);

    $tableau = explode('!', $response);

    $code = $tableau[1];
    $error = $tableau[2];
    $merchant_id = $tableau[3];
    $merchant_country = $tableau[4];
    $amount = $tableau[5]/100;
    $transaction_id = $tableau[6];
    $payment_means = $tableau[7];
    $transmission_date= $tableau[8];
    $payment_time = $tableau[9];
    $payment_date = $tableau[10];
    $response_code = $tableau[11];
    $payment_certificate = $tableau[12];
    $authorisation_id = $tableau[13];
    $currency_code = $tableau[14];
    $card_number = $tableau[15];
    $cvv_flag = $tableau[16];
    $cvv_response_code = $tableau[17];
    $bank_response_code = $tableau[18];
    $complementary_code = $tableau[19];
    $complementary_info= $tableau[20];
    $return_context = $tableau[21];
    $caddie = $tableau[22];
    $receipt_complement = $tableau[23];
    $merchant_language = $tableau[24];
    $language = $tableau[25];
    $customer_id = $tableau[26];
    $order_id = $tableau[27];
    $customer_email = $tableau[28];
    $customer_ip_address = $tableau[29];
    $capture_day = $tableau[30];
    $capture_mode = $tableau[31];
    $data = $tableau[32];

    if($code != 0){
        $error_message = "<h1>Erreur d'annulation du paiement electronique</h1> <p> Un probleme a été identifié, merci de reesayer plus tard. Description du probleme : " . $error . "</p>";
    } else {
        $error_message = "<h1>Paiement electronique annulé</h1> <p>Votre paiement de  $amount € à été annulé. </p>";
    }
    $this->view->setPart("error", $error_message);
    }

    public function addToShop(){
      $response = new \stdClass();
      $pictureId = $this->request->getPostParam("pictureId");
      $shopBag   = $this->services->getShop()->getShopBag();

      if(!in_array($pictureId, $shopBag)){
          $this->services->getShop()->addPicture($pictureId);
          $response->status = 200;
      } else {
          $response->status = 208;
      }
      $this->view->setPart('ajaxContent', json_encode($response));
    }

    public function removeToShop(){
      $response = new \stdClass();
      $pictureId = $this->request->getPostParam("pictureId");
      $shopBag = $this->services->getShop()->getShopBag();

      if(in_array($pictureId, $shopBag)){
          $this->services->getShop()->removePicture($pictureId);
          $response->status = 200;
      } else {
          $response->status = 404;
      }
      $this->view->setPart('ajaxContent', json_encode($response));
    }


}

?>
