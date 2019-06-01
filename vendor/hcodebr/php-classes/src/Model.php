<?php

namespace Hcode;

class Model {
    private $values = [];

    // __call é disparado ao invocar métodos inacessíveis em um contexto de objeto.
    // https://www.php.net/manual/pt_BR/language.oop5.overloading.php#object.call
    public function __call($name, $args)
    {
        
        // Pegando o inicio. get ou set
        $method = substr($name, 0, 3);
        // Pegando o nome do campo que foi chamado. ex: iduser
        $fieldName = substr($name, 3, strlen($name));

        switch ($method) {
            case 'get':
                // Setando o nome do campo
                return $this->values[$fieldName];
            break;
            case 'set':
                // Setando o nome do campo e atribuindo o valor do mesmo
                return $this->values[$fieldName] = $args[0];
            break;
        }
        exit;
    }

    public function setData($data = array())
    {
        /* Método que seta todos os valores da tabela do banco em
           atributos na class
        */
        foreach ($data as $key => $value) {
            // Criando dinamicamente os métodos que ficaram acessíveis depois
            $this->{"set".$key}($value);
        }
    }

    public function getValues($data = array())
    {
        return $this->values;
    }

}