<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model
{
    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const SECRET_IV = "HcodePhp7_Secret_IV";
    CONST ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSucesss";
    // const SECRET - Chave no tamanho de 16 caracteres ou mais (são valores fixos como 16, 32, 48)
    // que são utilizados para criptografar e descriptografar.

    public static function getFromSession()
    {
        $user = new User();

        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {
			$user->setData($_SESSION[User::SESSION]);
        }
        return $user;
    }
    // Checa se o usuário está logado e é administrador
    public static function checkLogin($inadmin = true)
    {
        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
        ) {
            // Não está logado
            return false;
        } else {
            // Se estamos logados e o usuário é um administrador.
            if($inadmin === true && (bool)(int)$_SESSION[User::SESSION]["iduser"] === true){
                return true;

            } else if ($inadmin === false) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function login($login, $pasword)
    {
        $sql = new Sql();

        // $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
        //     ":LOGIN"=>$login
        // ));

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

        // Se não encontrou resultado
        if(count($results) === 0)
        {
            // Utilizando o tratamento de erro do PHP e não um configurado por nós.
            // Por isso o motivo da barra. Agora estamos pegando a exception principal.
            throw new \Exception("Usuário inexistente ou senha inválida.", 1);
        }

        $data = $results[0];

        if(password_verify($pasword, $data["despassword"]) === true)
        {
            $user = new User();

            $user->setData($data);

            $data['desperson'] = utf8_encode($data['desperson']);

            $user->setData($data);

            // Criando uma sessão de login para sempre checar se o usuário está
            // logado. Foi criado aqui por conta de fins de organização e assim
            // mantemos a constante na classe que estamos utilizando.
            $_SESSION[User::SESSION] = $user->getValues();

            return $user;

        } else {
            throw new \Exception("Usuário inexistente ou senha inválida.", 1);
        }


    }
    public static function verifyLogin($inadmin = true)
    {
        // Se a sessão foi definida ou
        // Se a sessão existir ou
        // Se o idusario que está dentro dessa sessão não for maior do que 0
        // (ou seja se ele existir. Nesse caso estamos fazendo um casting) ou
        // Se o usuário é um administrador
        // if (
        //     !isset($_SESSION[User::SESSION])
        //     ||
        //     !$_SESSION[User::SESSION]
        //     ||
        //     !(int)$_SESSION[User::SESSION]["iduser"] > 0
        //     ||
        //     (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        // ) {
        //     header("Location: /admin/login");
        //     exit;
        // }
        //  Refatorando o código para User::checkLogin()
        if (!User::checkLogin($inadmin)) {

            if($inadmin){
                header("Location: /admin/login");
            } else {
                header("Location: /login");
            }
            exit;
        }
    }

    public static function logout()
    {
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll()
    {
        // Retorna toda a lista de usuários
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public function save()
    {
        $sql = new Sql();
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));


        $this->setData($results[0]);
    }

    public function get($iduser)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=> $iduser
        ));

        $data = $results[0];

        $data['desperson'] = utf8_encode($data['desperson']);

        $this->setData($data);
    }

    public function update()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));


        $this->setData($results[0]);

    }

    public function delete()
    {
        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

    public static function getForgot($email, $inadmin = true)
    {

        $sql = new Sql();

        // Pegando o email cadastrado no banco.
        $results = $sql->select("
            SELECT * FROM tb_persons a
            INNER JOIN tb_users b USING(idperson)
            WHERE a.desemail = :email;
        ", array(
            ":email"=> $email
        ));

        if (count($results) === 0) {
            throw new \Exception("Não foi possível recuperar a senha.");
        } else {
            $data = $results[0];

            var_dump($data["iduser"], $_SERVER["REMOTE_ADDR"]);
            exit;

            // $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
			// 	":iduser"=>$data["iduser"],
			// 	":desip"=>$_SERVER["REMOTE_ADDR"]
            // ));
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));


            if (count($results2) === 0) {
                throw new \Exception("Não foi possível recuperar a senha.");

            } else {
                $dataRecovery =  $results2[0];

                // Codificando em base_64 para enviar o link ao usuário em forma de texto.
                // $dataRecovery["idrecovery"] - criptando o valor da coluna idrecovery
                // base64_encode(openssl_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));

                $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);

                if($inadmin === true) {
                    $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

                } else {
                    $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
                }


                $mailer = new Mailer(
                    $data["desemail"],
                    $data["desperson"],
                    "Redefinir Senha da Hcode Store, forgot",
                    array(
                        "name" => $data["desperson"],
                        "link" => $link
                    )
                );

                $mailer->send();

                return $data;
            }
        }
    }

    public static function setError($msg)
    {
        $_SESSION[User::ERROR] = $msg;
    }

    public static function getError()
    {
        $msg = (isset($_SESSION[User::ERROR]) &&  $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";

        User::clearError();

        return $msg;
    }

    public static function clearError()
    {
        $_SESSION[User::ERROR] = NULL;
    }

    public static function setSuccess($msg)
    {
        $_SESSION[User::SUCCESS] = $msg;
    }

    public static function getSuccess()
    {
        $msg = (isset($_SESSION[User::SUCCESS]) &&  $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : "";

        User::clearSuccess();

        return $msg;
    }

    public static function clearSuccess()
    {
        $_SESSION[User::SUCCESS] = NULL;
    }

    public static function setErrorRegister($msg)
	{
		$_SESSION[User::ERROR_REGISTER] = $msg;
    }

	public static function getErrorRegister()
	{
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
		User::clearErrorRegister();
		return $msg;
	}

	public static function clearErrorRegister()
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
    }

    public static function checkLoginExist($login)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
        ]);
        // Se retornou algum login que já existe é true
		return (count($results) > 0);
	}

    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost'=>12
        ]);
    }
}
