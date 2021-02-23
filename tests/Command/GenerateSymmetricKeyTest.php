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
        $this->commandTester->execute([]);
        $output = explode("\n", $this->commandTester->getDisplay());

        $symmetricKey = new SymmetricKey(\hex2bin($output[0]));

        $builder = PasetoBuilderFactory::localPasetoFactory($symmetricKey);
        $builder->setClaims(['test' => 'value']);

        // TODO: use parser factory
        $parser = new Parser();
        $parser->setKey($symmetricKey);

        $this->assertEquals(['test' => 'value'], $parser->parse($builder->toString())->getClaims());
    }

}