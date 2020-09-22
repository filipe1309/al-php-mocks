<?php

namespace Alura\Leilao\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\{ Lance, Leilao, Usuario };
use Alura\Leilao\Service\{ Avaliador, Encerrador };


class LeilaoDaoMock extends LeilaoDao
{
    private $leiloes = [];

    public function salva(Leilao $leilao): void
    {
        // Ao inves de salvar na base, salva em um array na memoria
        $this->leiloes[] = $leilao;
    }

    public function recuperarNaoFinalizados(): array
    {
        return array_filter($this->leiloes, fn (Leilao $leilao) => !$leilao->estaFinalizado());
    }

    public function recuperarFinalizados(): array
    {
        return array_filter($this->leiloes, fn (Leilao $leilao) => $leilao->estaFinalizado());
    }

    public function atualiza(Leilao $leilao)
    {
    }
}

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

        $leilaoDao = new LeilaoDaoMock();
        $leilaoDao->salva($fiat147);
        $leilaoDao->salva($variant);

        $encerrar = new Encerrador($leilaoDao);
        $encerrar->encerra();

        $leiloes = $leilaoDao->recuperarFinalizados();
        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
        self::assertEquals('Fiat 147 0km', $leiloes[0]->recuperarDescricao());
        self::assertEquals('Variant 1972 0km', $leiloes[1]->recuperarDescricao());
    }
}
