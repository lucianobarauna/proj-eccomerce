<?php 

// Template padrão do projeto.
namespace Hcode;

use Rain\Tpl;

class Page
{
    private $tpl;
    private $options = [];
    private $defaults = [
        "header"=>true,
        "footer"=>true,
        "data" => []
    ];

    public function __construct($opts = array(), $tpl_dir = "/views/") {
        // $opts para a classe
        // config rain tpl
        
        // array_merge - sobrescreve o primeiro array com o segundo.
        $this->options = array_merge($this->defaults, $opts);
       

        // $_SERVER["DOCUMENT_ROOT"] - traz o caminho do root
        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir, # diretório de templates
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/", # diretório de cache do template
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure( $config );

        // Criando o template.
        $this->tpl = new Tpl;
        $this->setData($this->options["data"]);

        // Montando o cabeçalho header em todos os templates.
        if ($this->options["header"] === true) $this->tpl->draw("header");

    }
    public function __destruct() {
        // Quando sair do cache do php é que inserimos o footer. Aqui vai conter
        // os arquivos JS.
        if ($this->options["footer"] === true) $this->tpl->draw("footer");
    }

    private function setData($data = array())
    {
        // Pegando as variáveis e passando para o template
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }

    public function setTpl($name, $data = array(), $returnHTML = false)
    {
        // é o argumento que diz se vamos ter que jogar o html par ao cliente.
        $this->setData($data);
        return $this->tpl->draw($name, $returnHTML);
    }
    
    
}


