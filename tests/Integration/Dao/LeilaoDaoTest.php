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

    public function testInsercaoEBuscaDevemFuncionar()
    {
        // Arrange - Given
        $leilao = new Leilao('Variant 0Km');
        $leilaoDao = new LeilaoDao(self::$pdo);

        $leilaoDao->salva($leilao);
        // Act -When
        $leiloes = $leilaoDao->recuperarNaoFinalizados();
        
        // Assert - Then
        self::assertCount(1, $leiloes);
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame('Variant 0Km', $leiloes[0]->recuperarDescricao());        
    }

    public function tearDown(): void
    {
        self::$pdo->rollBack();
    }
}
