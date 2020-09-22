<?php

namespace Alura\Leilao\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\{ Lance, Leilao, Usuario };
use Alura\Leilao\Service\{ Avaliador, Encerrador };

class EncerradorTest extends TestCase
{
    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $fiat147 = new Leilao(
            'Fiat 147 0km',
            new \DateTimeImmutable('8 days ago')
        );

        $variant = new Leilao(
            'Variant 1972 0km',
            new \DateTimeImmutable('10 days ago')
        );

        $leilaoDao = $this->createMock(LeilaoDao::class);
        $leilaoDao
            ->method('recuperarNaoFinalizados')
            ->willReturn([$fiat147, $variant]);
        $leilaoDao
            ->method('recuperarFinalizados')
            ->willReturn([$fiat147, $variant]);
        $leilaoDao
            ->expects($this->exactly(2)) // $this->once()
            ->method('atualiza')
            ->withConsecutive([$fiat147], [$variant]);

        $encerrar = new Encerrador($leilaoDao);
        $encerrar->encerra();

        $leiloes = [$fiat147, $variant];
        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
    }
}
