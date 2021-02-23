<?php


namespace Mehdibo\Bundle\PasetoBundle\Tests\Command;


use Mehdibo\Bundle\PasetoBundle\Command\GenerateAsymmetricKey;
use Mehdibo\Bundle\PasetoBundle\Factories\PasetoBuilderFactory;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use ParagonIE\Paseto\Parser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateAsymmetricKeyTest extends TestCase
{

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $command = new GenerateAsymmetricKey();
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandShowsKeys(): void
    {

        $this->assertEquals(Command::SUCCESS, $this->commandTester->execute([]));

        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(5, $output);
        $this->assertEquals("Private key", $output[0]);
        $this->assertEquals(128, \strlen($output[1]));
        $this->assertEquals("Public key", $output[2]);
        $this->assertEquals(64, \strlen($output[3]));
        $this->assertEmpty($output[4]);
    }

    public function testCommandGeneratesValidKeys(): void
    {
        $this->commandTester->execute([]);
        $output = explode("\n", $this->commandTester->getDisplay());

        $secretKey = new AsymmetricSecretKey(\hex2bin($output[1]));
        $publicKey = $secretKey->getPublicKey();

        $builder = PasetoBuilderFactory::publicPasetoFactory($secretKey);
        $builder->setClaims(['test' => 'value']);

        // TODO: use parser factory
        $parser = new Parser();
        $parser->setKey($publicKey);

        $this->assertEquals(['test' => 'value'], $parser->parse($builder->toString())->getClaims());
    }

}