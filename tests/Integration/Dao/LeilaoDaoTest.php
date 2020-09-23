<?php

namespace Alura\Leilao\Tests\Integration\Dao;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use  Alura\Leilao\Infra\ConnectionCreator;
use PHPUnit\Framework\TestCase;

class LeilaoDaoTest extends TestCase
{
    private \PDO $pdo;

    public function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->exec('CREATE TABLE leiloes (
            id INTEGER PRIMARY KEY,
            descricao TEXT,
            finalizado BOOL,
            dataInicio TEXT
        );');
        $this->pdo->beginTransaction();
    }

    public function testInsercaoEBuscaDevemFuncionar()
    {
        // Arrange - Given
        $leilao = new Leilao('Variant 0Km');
        $leilaoDao = new LeilaoDao($this->pdo);

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
        $this->pdo->rollBack();
    }
}
