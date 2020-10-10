<?php

namespace Metardata\App\Services;

use Metardata\App\Models\Picture;

class DataExtractService
{
    private $fileMetadata;
    private $realPath;
    private $request;

    public function __construct($request){
        $this->fileMetadata = array();
        $this->realPath     = realpath("tools/exiftool.exe");
        $this->request      = $request;
    }

    public function extract(Picture $picture, $resetCache=false) {
        if(!$resetCache){
            $metaSession = $this->request->getSession("meta");

            if(!$metaSession) {
                $metaSession = [];
            }

            $pictureMeta = null;
            if(key_exists($picture->getId(), $metaSession)) {
                $pictureMeta = $metaSession[$picture->getId()];
            }

            if($pictureMeta) {
                // load from cache
                if(!$picture->getMeta()) {
                    $picture->setMeta($pictureMeta);
                }
            } else {
                // load from exiftool extraction
                exec(EXIF_PATH.' -g1 -json '.$picture->getPath(), $output);
                $output = implode($output);
                $output = json_decode($output, true);
                $sourceFile = $output[0];
                $picture->setMeta($sourceFile);
                array_push($metaSession, $sourceFile);

                $this->request->setSession("meta", $metaSession);
            }
        } else {
            // load from exiftool extraction
            exec(EXIF_PATH.' -g1 -json '.$picture->getPath(), $output);
            $output = implode($output);
            $output = json_decode($output, true);
            $sourceFile = $output[0];
            $picture->setMeta($sourceFile);
            $this->request->setSession("meta", $sourceFile);
        }
    }

    public function getMetadata() {
        return $this->fileMetadata[0];
    }

    public function updateMeta(Picture $picture, $updatedMeta) {
        $command = EXIF_PATH;
        foreach ($updatedMeta as $key => $value) {
          if($key !== "path") $command .= " -".$key."=\"".$value."\"";
        }
        $command .= " ".$picture->getPath();

        if(sizeof($updatedMeta) <= 1) {
            return true;
        }

        exec($command, $output);

        exec(EXIF_PATH.' -delete_original! public/images');

        if($output[0] && strpos($output[0], "updated")) return true;
        return false;
    }
}
