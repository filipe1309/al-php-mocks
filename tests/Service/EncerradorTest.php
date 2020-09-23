<?php

namespace Alura\Leilao\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\{ Lance, Leilao, Usuario };
use Alura\Leilao\Service\{ Avaliador, Encerrador, EnviadorEmail };

class EncerradorTest extends TestCase
{
    private Encerrador $encerrador;
    private EnviadorEmail $enviadorEmail;
    private Leilao $fiat147;
    private Leilao $variant;

    protected function setUp(): void
    {
        $this->fiat147 = new Leilao(
            'Fiat 147 0km',
            new \DateTimeImmutable('8 days ago')
        );

        $this->variant = new Leilao(
            'Variant 1972 0km',
            new \DateTimeImmutable('10 days ago')
        );

        $leilaoDao = $this->createMock(LeilaoDao::class);
        // $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
        //     ->setConstructorArgs([new \PDO('sqlite::memory:')])
        //     ->getMock();
        $leilaoDao
            ->method('recuperarNaoFinalizados')
            ->willReturn([$this->fiat147, $this->variant]);
        $leilaoDao
            ->method('recuperarFinalizados')
            ->willReturn([$this->fiat147, $this->variant]);
        $leilaoDao
            ->expects($this->exactly(2)) // $this->once()
            ->method('atualiza')
            ->withConsecutive([$this->fiat147], [$this->variant]);

        $this->enviadorEmail = $this->createMock(EnviadorEmail::class);

        $this->encerrador = new Encerrador($leilaoDao, $this->enviadorEmail);
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $this->encerrador->encerra();

        $leiloes = [$this->fiat147, $this->variant];
        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
    }

    public function testDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmail()
    {
        $e = new \DomainException('Erro a enviar email');
        $this->enviadorEmail
            ->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willThrowException($e);
            
        $this->encerrador->encerra();
    }
}
