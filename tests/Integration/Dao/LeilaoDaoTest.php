<?php

namespace Alura\Leilao\Tests\Integration\Dao;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use  Alura\Leilao\Infra\ConnectionCreator;
use PHPUnit\Framework\TestCase;

class LeilaoDaoTest extends TestCase
{
    private $pdo;

    public function setUp(): void
    {
        $this->pdo = ConnectionCreator::getConnection();
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
        $this->pdo->exec('DELETE FROM leiloes');
    }
}
