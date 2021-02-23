<?php


namespace Mehdibo\Bundle\PasetoBundle\Command;


use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAsymmetricKey extends Command
{

    /**
     * @var string
     */
    protected static $defaultName = "mehdibo:paseto:generate-asymmetric";

    protected function configure(): void
    {
        $this->setDescription("Generate a asymmetric keys")
            ->setHelp(<<< HELP
This command will generate a pair of Private/Public keys to use for public Paseto tokens.
The keys will be printed as a HEX string
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $rawKey = \sodium_crypto_sign_keypair();
        } catch (\SodiumException $e) {
            $output->write($e->getMessage());
            return Command::FAILURE;
        }

        try {
            $privateKey = new AsymmetricSecretKey($rawKey);
        } catch (\Exception $e) {
            $output->write($e->getMessage());
            return Command::FAILURE;
        }

        $rawPrivateKey = $privateKey->raw();
        try {
            $rawPublicKey = $privateKey->getPublicKey()->raw();
        } catch (\Exception $e) {
            $output->write($e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln([
            "Private key",
            \bin2hex($rawPrivateKey),
            "Public key",
            \bin2hex($rawPublicKey)
        ]);

        return Command::SUCCESS;
    }

}