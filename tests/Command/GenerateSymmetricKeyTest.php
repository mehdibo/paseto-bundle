<?php

namespace Mehdibo\Bundle\PasetoBundle\Tests\Command;

use Mehdibo\Bundle\PasetoBundle\Command\GenerateSymmetricKey;
use Mehdibo\Bundle\PasetoBundle\Factories\PasetoBuilderFactory;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Parser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateSymmetricKeyTest extends TestCase
{

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $command = new GenerateSymmetricKey();
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandShowsKey(): void
    {
        $this->assertEquals(Command::SUCCESS, $this->commandTester->execute([]));
        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEquals(64, \strlen($output[0]));
        $this->assertEmpty($output[1]);
    }

    public function testCommandGeneratesValidKeys(): void
    {
        $claims = [
            'drink_milk' => 'stronk_bonks'
        ];

        $this->commandTester->execute([]);
        $output = explode("\n", $this->commandTester->getDisplay());

        $decodedKey = \hex2bin($output[0]);
        if ($decodedKey === false) {
            $this->fail("Failed decoding key");
        }
        $secretKey = new SymmetricKey($decodedKey);

        $token = (PasetoBuilderFactory::localPasetoFactory($secretKey))
            ->setClaims($claims)
            ->toString();

        $parsedToken = (new Parser())
            ->setKey($secretKey)
            ->parse($token);

        $this->assertEquals($claims, $parsedToken->getClaims());
    }
}
