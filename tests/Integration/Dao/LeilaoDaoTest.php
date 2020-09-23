<?php

namespace Alura\Leilao\Tests\Integration\Dao;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use  Alura\Leilao\Infra\ConnectionCreator;
use PHPUnit\Framework\TestCase;

class LeilaoDaoTest extends TestCase
{
    public function testInsercaoEBuscaDevemFuncionar()
    {
        // Arrange - Given
        $leilao = new Leilao('Variant 0Km');
        $pdo = ConnectionCreator::getConnection();
        $leilaoDao = new LeilaoDao($pdo);

        $leilaoDao->salva($leilao);
        // Act -When
        $leiloes = $leilaoDao->recuperarNaoFinalizados();
        
        // Assert - Then
        self::assertCount(1, $leiloes);
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame('Variant 0Km', $leiloes[0]->recuperarDescricao());
        
        // Tear down
        $pdo->exec('DELETE FROM leiloes');
    }
}
