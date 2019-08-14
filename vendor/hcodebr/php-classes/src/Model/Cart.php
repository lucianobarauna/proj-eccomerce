<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model
{
    const SESSION = "Cart";
    const SESSION_ERROR = "CartError";

    public static function getFromSession()
    {
        $cart = new Cart();
        // Checando se o carrinho já está na sessão e foi inserido no banco
        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0 ) {
            // Carregando o carrinho
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        } else {
            // Pegando o carrinho pela sessão dele no banco
            $cart->getFromSessionID();

            // Se não conseguiu pegar o carrinho
            if(!(int)$cart->getidcart() > 0){
                // Criando um novo carrinho
                // Utilizando a sessão conseguimos criar um carrinho abandonado ou podemos até
                // enviar um email para o usuário.
                $data = [
                    'dessessionid'=> session_id()
                ];

                if(User::checkLogin(false)) {
                    $user = User::getFromSession();
                    $data['iduser'] = $user->getiduser();
                }

                $cart->setData($data);
                $cart->save();
                $cart->setToSession();


            }
        }

        return $cart;
    }

    public function setToSession()
    {
        // Colocando o carrinho na sessão
        // O método aqui não é estático pq precisamos fazer o uso da variável $this.
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    // Pega o carrinho pela sessão no banco
    public function getFromSessionID()
    {
        $sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'=>session_id()
		]);
		if (count($results) > 0) {
            $this->setData($results[0]);
		}

    }

    // Pega o carrinho pelo id
    public function get(int $idcart)
    {
        $sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=>$idcart
        ]);

		if (count($results) > 0) {
            $this->setData($results[0]);
		}

    }
    public function save()
    {
        // Salvando o carrinho
        $sql = new Sql();
		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);
		$this->setData($results[0]);
    }

    public function addProduct(Product $product)
    {
        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)",[
            ':idcart'=>$this->getidcart(),
            ':idproduct'=>$product->getidproduct()
        ]);
        $this->getCalculateTotal();


    }

    public function removeProduct(Product $product, $all = false)
	{
		$sql = new Sql();
		if ($all) {
            // Se for não for nulo deleta tudo
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);
		} else {
            // Se for não for nulo deleta apenas um
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);
		}
		$this->getCalculateTotal();
    }

    public function getProducts()
    {
        $sql = new Sql();

        $rows = $sql->select("
			SELECT b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
			FROM tb_cartsproducts a
			INNER JOIN tb_products b ON a.idproduct = b.idproduct
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL
			GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
			ORDER BY b.desproduct
		", [
			':idcart'=>$this->getidcart()
        ]);
		return Product::checkList($rows);
    }

    public function getProductsTotals()
	{
 		$sql = new Sql();
 		$results = $sql->select("
			SELECT COUNT(*) AS nrqtd, SUM(b.vlprice) AS vlprice, SUM(b.vlwidth) AS vlwidth, SUM(b.vlheight) AS vlheight, SUM(b.vllength) AS vllength, SUM(b.vlweight) AS vlweight
			FROM tb_cartsproducts a
			INNER JOIN tb_products b ON a.idproduct = b.idproduct
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL
		", [
			':idcart'=>$this->getidcart()
		]);
 		if (count($results) > 0) {
 			return $results[0];
 		} else {
 			return [];
 		}
    }

    //  Monta o frete de acordo com a tabela dos correios
    public function setFreight($zipcode)
    {
        $nrzipcode = str_replace('-', '', $zipcode);

        $totals = $this->getProductsTotals();

        if($totals['nrqtd'] > 0) {
            if($totals['vlheight'] < 2) $totals['vlheight'] = 2;
            if($totals['vllength'] < 16) $totals['vllength'] = 16;

            // Monta a query para http
            $qs = http_build_query([
                "nCdEmpresa"=>"",
                "sDsSenha"=>"",
                "nCdServico"=>"40010",
                "sCepOrigem"=>"09853120",
                "sCepDestino"=>$nrzipcode,
                "nVlPeso"=>$totals['vlweight'],
                "nCdFormato"=>"1",
                "nVlComprimento"=>$vllength,
                "nVlAltura"=>$totals['vlheight'],
                "nVlLargura"=>$totals['vlwidth'],
                "nVlDiametro"=>"0",
                "sCdMaoPropria"=>"S",
                "nVlValorDeclarado"=>$totals['vlprice'],
                "sCdAvisoRecebimento"=>"S"
            ]);

            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

            $result = $xml->Servicos->cServico;

            if($result->MsgErro != '') {
                Cart::setMsgError($result->MsgErro);
            } else {
                Cart::clearMsgError();
            }

            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);

            $this->save();

            return $result;


        } else {

        }
    }

    public static function formatValueToDecimal($value):float
    {
        $value = str_replace('.', '', $value);
        return str_replace(',', '.', $value);
    }

    public static function setMsgError($msg)
    {
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }

    public static function getMsgError($msg)
    {
        // $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

        // Cart::clearMsgError();

        // return $msg;
    }

    public static function clearMsgError($msg)
    {
        $_SESSION[Cart::SESSION_ERROR] = NULL;
    }

    public function updateFreight()
    {
        if($this->getdeszipcode() != '') {
            $this->setFreight($this->getdeszipcodde());
        }
    }

    // Adiciona o total do frete ao método getValues do pai.
    public function getValues()
    {
        $this->getCalculateTotal();

        return Parent::getValues();
    }

    public function getCalculateTotal()
    {
        $this->updateFreight();

        $totals = $this->getProductsTotals();

        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight());
    }

}
