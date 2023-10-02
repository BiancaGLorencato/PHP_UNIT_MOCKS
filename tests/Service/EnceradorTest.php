<?php

namespace Alura\Leilao\Tests\Service;

use PDO;
use DateTimeImmutable;
use Alura\Leilao\Model\Leilao;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Service\EnviadorEmail;
use DomainException;

class EnceradorTest extends TestCase
{
    private $encerrador;
    private $fiat147;
    private $variant;
    /** @var MockObject */
    private $enviadorEmail;


    protected function setUp() : void 
    {
        $this->fiat147 = new Leilao(
            'Fiat',
            new \DateTimeImmutable('8 days ago')
        );
        $this->variant = new Leilao(
                'Variante',
            new \DateTimeImmutable('10 days ago')

        );

        $leilaoDao = $this->createMock(LeilaoDao::class);
        $leilaoDao->method('recuperarNaoFinalizados')->willReturn([$this->fiat147, $this->variant]);
        $leilaoDao->expects($this->exactly(2))->method('atualiza')
                ->withConsecutive(
                    [$this->fiat147],
                    [$this->variant]
                );
        
     
        $this->enviadorEmail = $this->createMock(EnviadorEmail::class);

        $this->encerrador = new Encerrador($leilaoDao, $this->enviadorEmail);
    }
    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
    
        $this->encerrador->encerra();

        $leilaoF = [$this->fiat147, $this->variant];
        self::assertCount(2, $leilaoF);
        self::assertEquals('Fiat', $leilaoF[0]->recuperarDescricao());
        self::assertEquals('Variante', $leilaoF[1]->recuperarDescricao());

    }

    public function testDeveContinuarProcessoAoEncontrarErroAoEnviarEmail()
    {
        $e = new DomainException('Erro ao enviar email');

        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')->willThrowException($e);

        $this->encerrador->encerra();
    }

    public function testeSoDeveEnviarEmailSeFinalizado()
    {
        $this->enviadorEmail->expects($this->exactly(2))
        ->method('notificarTerminoLeilao')->willReturnCallBack(function(Leilao $leilao){
            static::assertTrue($leilao->estaFinalizado());
        });
        $this->encerrador->encerra();
    }
}