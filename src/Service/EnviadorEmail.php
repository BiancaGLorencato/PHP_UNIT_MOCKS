<?php 

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;
use DomainException;

class EnviadorEmail
{
    public function notificarTerminoLeilao(Leilao $leilao)
    {
       $sucesso = mail('biancs.24gl@gmail.com',   'Leilao finalizado', 'O leilão para' . $leilao->recuperarDataInicio(). ' foi finalizado' );
    
        if(!$sucesso){
            throw new DomainException('Erro ao enviar email');
        }
    }
}