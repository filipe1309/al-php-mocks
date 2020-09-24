<?php

namespace Alura\Leilao\Tests\Integration\Dao;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use  Alura\Leilao\Infra\ConnectionCreator;
use PHPUnit\Framework\TestCase;

class LeilaoDaoTest extends TestCase
{
    private static \PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new \PDO('sqlite::memory:');
        self::$pdo->exec('CREATE TABLE leiloes (
            id INTEGER PRIMARY KEY,
            descricao TEXT,
            finalizado BOOL,
            dataInicio TEXT
        );');
    }

    public function setUp(): void
    {
        self::$pdo->beginTransaction();
    }

    /** @dataProvider leiloes */
    public function testBuscaLeiloesNaoFinalizados(array $leiloes)
    {
        // Arrange - Given
        $leilaoDao = new LeilaoDao(self::$pdo);
        foreach ($leiloes as $leilao) {
            $leilaoDao->salva($leilao);
        }

        // Act -When
        $leiloes = $leilaoDao->recuperarNaoFinalizados();
        
        // Assert - Then
        self::assertCount(1, $leiloes);
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame('Variant 0Km', $leiloes[0]->recuperarDescricao());  
        self::assertFalse($leiloes[0]->estaFinalizado());
    }

    /** @dataProvider leiloes */
    public function testBuscaLeiloesFinalizados(array $leiloes)
    {
        // Arrange - Given
        $leilaoDao = new LeilaoDao(self::$pdo);
        foreach ($leiloes as $leilao) {
            $leilaoDao->salva($leilao);
        }

        // Act -When
        $leiloes = $leilaoDao->recuperarFinalizados();
        
        // Assert - Then
        self::assertCount(1, $leiloes);
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame('Fiat 147 0KM', $leiloes[0]->recuperarDescricao());
        self::assertTrue($leiloes[0]->estaFinalizado());
    }

    public function testAoAtualizarLeilaoStatusDeveSerAlterado()
    {
        // Arrange - Given
        $leilao = new Leilao('Brasilia amarela');
        $leilaoDao = new LeilaoDao(self::$pdo);
        $leilao = $leilaoDao->salva($leilao);
        $leilao->finaliza();

        // Intermediate asserts (brake AAA)
        $leiloes = $leilaoDao->recuperarNaoFinalizados();
        self::assertCount(1, $leiloes);
        self::assertSame('Brasilia amarela', $leiloes[0]->recuperarDescricao());
        self::assertFalse($leiloes[0]->estaFinalizado());

        // Act - When
        $leilaoDao->atualiza($leilao);

        // Assert - Then
        $leiloes = $leilaoDao->recuperarFinalizados();
        self::assertCount(1, $leiloes);
        self::assertSame('Brasilia amarela', $leiloes[0]->recuperarDescricao());
        self::assertTrue($leiloes[0]->estaFinalizado());
    }

    public function tearDown(): void
    {
        self::$pdo->rollBack();
    }

    public function leiloes()
    {
        $naoFinalizado = new Leilao('Variant 0Km');
        $finalizado = new Leilao('Fiat 147 0KM');
        $finalizado->finaliza();

        return [
            [
                [$naoFinalizado, $finalizado]
            ]
        ];
    }
}
