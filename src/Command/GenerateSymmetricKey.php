<?php

namespace Mehdibo\Bundle\PasetoBundle\Command;

use ParagonIE\Paseto\Protocol\Version2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSymmetricKey extends Command
{

    /**
     * @var string
     */
    protected static $defaultName = "mehdibo:paseto:generate-symmetric-key";

    protected function configure(): void
    {
        $this->setDescription("Generate a symmetric key")
            ->setHelp(<<< HELP
This command allows you to generate a random 32 bytes key to use for local Paseto tokens
The key will be printed as a HEX string
HELP
);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $rawKey = \random_bytes(Version2::SYMMETRIC_KEY_BYTES);
        } catch (\Exception $e) {
            $output->write($e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln(\bin2hex($rawKey));

        return Command::SUCCESS;
    }

}