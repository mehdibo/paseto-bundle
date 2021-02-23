<?php


namespace Mehdibo\Bundle\PasetoBundle\Tests\Command;


use Mehdibo\Bundle\PasetoBundle\Command\GenerateAsymmetricKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateAsymmetricKeyTest extends TestCase
{

    public function testExecute(): void
    {
        $command = new GenerateAsymmetricKey();
        $commandTester = new CommandTester($command);

        $this->assertEquals(Command::SUCCESS, $commandTester->execute([]));

        $output = explode("\n", $commandTester->getDisplay());
        $this->assertCount(5, $output);
        $this->assertEquals("Private key", $output[0]);
        $this->assertEquals(128, \strlen($output[1]));
        $this->assertEquals("Public key", $output[2]);
        $this->assertEquals(64, \strlen($output[3]));
        $this->assertEmpty($output[4]);
    }

}