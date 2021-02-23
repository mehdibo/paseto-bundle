<?php

namespace Mehdibo\Bundle\PasetoBundle\Tests\Command;


use Mehdibo\Bundle\PasetoBundle\Command\GenerateSymmetricKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateSymmetricKeyTest extends TestCase
{

    public function testCommandShowsKey(): void
    {
        $command = new GenerateSymmetricKey();
        $commandTester = new CommandTester($command);

        $this->assertEquals(Command::SUCCESS, $commandTester->execute([]));

        $output = explode("\n", $commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEquals(64, \strlen($output[0]));
        $this->assertEmpty($output[1]);
    }

}