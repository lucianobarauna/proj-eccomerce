<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model
{
    const SESSION = "Cart";

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

}
