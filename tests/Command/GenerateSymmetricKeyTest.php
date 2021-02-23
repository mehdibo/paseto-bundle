<?php

namespace Mehdibo\Bundle\PasetoBundle\Tests\Command;


use Mehdibo\Bundle\PasetoBundle\Command\GenerateSymmetricKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateSymmetricKeyTest extends TestCase
{

    public function testExecute(): void
    {
        $command = new GenerateSymmetricKey();
        $commandTester = new CommandTester($command);

        $this->assertEquals(Command::SUCCESS, $commandTester->execute([]));

        $output = $commandTester->getDisplay();
        $this->assertIsString($output);
        $this->assertEquals(65, strlen($output));
    }

}