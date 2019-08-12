<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Product extends Model
{


    public static function listAll()
    {
        // Retorna toda a lista de produtos
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }

    public static function checkList($list)
    {
        foreach ($list as &$row) {
            // &$row está com a referência de list então ele está alterando ela.
            // deixando nesse caso de ser o index do forEach
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
        }

        return $list;
    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl(),
            ));

        $this->setData($results[0]);
    }

    public function get($idproduct)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ":idproduct"=>$idproduct,
        ]);

        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();

        $sql->select("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ":idproduct"=>$this->getidproduct()
        ]);
    }

    public function checkPhoto()
    {
        // Checando se tem uma foto o produto. Se não tiver nós colocamos uma
        // foto padrão.
        if(file_exists(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
            "res" . DIRECTORY_SEPARATOR .
            "site" . DIRECTORY_SEPARATOR .
            "img". DIRECTORY_SEPARATOR .
            "products" . DIRECTORY_SEPARATOR .
            $this->getidproduct() . ".jpg"
            )) {
                $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
        } else {
            $url = "/res/site/img/product.jpg";
        }

        return $this->setdesphoto($url);
    }

    public function getValues()
    {
        $this->checkPhoto();
        // Reescrevendo o método getValues que vem de model.

        // $page->setTpl("products-update", [
            //     'product'=>$product->getValues()
            // ]);

        $values = parent::getValues();
        return $values;
    }

    public function setPhoto($file)
    {
        // Deixa o usuário subir qualquer tipo de imagem e aproveitamos que
        // o php cria um arquivo temporario para realizar a conversão para um
        // arquivo de imagem do tipo jpg.
        $extension =  explode('.', $file['name']);
        $extension =  end($extension);

        // Convertendo a imagem para a biblioteca GD
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            $image = imagecreatefromjpeg($file["tmp_name"]);
            break;

            case 'gif':
            $image = imagecreatefromgif($file["tmp_name"]);
            break;

            case 'png':
            $image = imagecreatefrompng($file["tmp_name"]);
            break;

        }

        $dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
            "res" . DIRECTORY_SEPARATOR .
            "site" . DIRECTORY_SEPARATOR .
            "img". DIRECTORY_SEPARATOR .
            "products" . DIRECTORY_SEPARATOR .
            $this->getidproduct() . ".jpg";

        // Criando a imagem como jpg
        imagejpeg($image, $dist);
        // Removendo o ponteiro
        imagedestroy($image);

        $this->checkPhoto();

    }

    public function getFromURL($desurl)
    {
        $sql = new Sql();

        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
            ':desurl' =>$desurl
        ]);
        $this->setData($rows[0]);
    }

    public function getCategories()
    {
        $sql = new Sql();

        return $sql->select("
            SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct
        ",[
            'idproduct'=>$this->getidproduct()
        ]);
    }


}
