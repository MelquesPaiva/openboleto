<?php

require 'caixaSample.php';

use Source\OpenBoleto\Banco\Itau;
use Source\OpenBoleto\Agente;
use JasperPHP\Report;
//use JasperPHP\ado\TTransaction;
//use JasperPHP\ado\TLoggerHTML;

class Boleto
{
    /* Variavel que armazenara os dados do boleto
    / @var array();
    */
    private $data = array();
    /*
    * método __set()
    * executado sempre que uma propriedade for atribuída.
    */
    public function __set($prop, $value)
    {
        // verifica se existe método set_<propriedade>
        if (method_exists($this, 'set_'.$prop))
        {
            // executa o método set_<propriedade>
            call_user_func(array($this, 'set_'.$prop), $value);
        }
        else
        {
            if ($value === NULL)
            {
                unset($this->data[$prop]);
            }
            else
            {
                // atribui o valor da propriedade
                $this->data[$prop] = $value;
            }
        }
    }
    /*
    * método __get()
    * executado sempre que uma propriedade for requerida
    */
    public function __get($prop)
    {
        // verifica se existe método get_<propriedade>
        if (method_exists($this, 'get_'.$prop))
        {
            // executa o método get_<propriedade>
            return call_user_func(array($this, 'get_'.$prop));
        }
        else
        {
            // retorna o valor da propriedade
            if (isset($this->data[$prop]))
            {
                return ($this->data[$prop]);
            }
        }

        return null;
    }

    public function __construct($sequencial = null, $boleto = null)
    {
        //
        // aqui voce pode acessar sua base de dados e coletar os dados do boleto e preencher os campos abaixo
        //

        $boleto->getOutput();
        $this->data = array_merge($this->data,$boleto->getData());
    }

    /* método para interceptar  a requisição e adicionar o codigo html necessario para correta exibição do demostrativo    */
    public function get_demonstrativo()
    {
        return '<table>
        <tr>
        <td>'.implode('<br>',$this->data['demonstrativo']).
            '</td>
        </tr>
        <table>';
    }

    /* método para interceptar  a requisição e adicionar o codigo html necessario para correta exibição das instrucoes    */
    public function get_instrucoes()
    {
        return '<table>
        <tr>
        <td>'.implode('<br>',$this->data['instrucoes']).'
        </td>
        </tr>
        <table>';
    }

    /* este metodo esta aqui para manter compatibilidade do jxml criado para o meu sistema*/
    public function get_carteiras_nome()
    {
        return $this->data['carteira'];
    }

}
// altere aqui para o nome do arquivo de configuração no diretorio config desativado mas pode ser usado por usuarios avançados
//JasperPHP\ado\TTransaction::open('dev');

// instancição do objeto :1 parametro: caminho do layout do boleto , 2 parametro :  array com os parametros para consulta no banco para localizar o boleto
// pode ser passado como paramtro um array com os numeros dos boletos que serão impressos desde que criado sql dentro do arquivo jrxml(desativado nesse exemplo)

$report =new Report(__DIR__ . "/../xml/bol01Files/boletoA4.jrxml",array());
//$report =new JasperPHP\Report("bol01Files/boletoA4.jrxml",array());

\JasperPHP\Instructions::prepare($report);    // prepara o relatorio lendo o arquivo
$report->dbData = array(new Boleto(1, $boleto),new boleto(2, $boleto)); // aqui voce pode construir seu array de boletos em qualquer estrutura incluindo

$report->generate(array());


$report->out();                     // gera o pdf
$pdf  = \JasperPHP\PdfProcessor::get();       // extrai o objeto pdf de dentro do report

/** @var TCPDF $pdf */
$pdf->Output('boleto.pdf',"I");  // metodo do TCPF para gerar saida para o browser