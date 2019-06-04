<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model
{
    const SESSION = "User";

    public static function login($login, $pasword)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
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
        if (
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ) {
            header("Location: /admin/login");
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
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
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

        $this->setData($results[0]);
    }

    public function update()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
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

    
}
