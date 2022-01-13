<?php


namespace Mehdibo\Bundle\PasetoBundle\Command;

use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoParser;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoParser;
use ParagonIE\Paseto\Exception\PasetoException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DecodeToken extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = "mehdibo:paseto:decode-token";

    private LocalPasetoParser $localParser;
    private PublicPasetoParser $publicParser;

    public function __construct(
        LocalPasetoParser $localParser,
        PublicPasetoParser $publicParser,
        string $name = null
    ) {
        parent::__construct($name);
        $this->localParser = $localParser;
        $this->publicParser = $publicParser;
    }

    protected function configure(): void
    {
        $this->setDescription("Decode a Paseto token")
            ->addArgument(
                "token",
                InputArgument::REQUIRED,
                "Token to decode"
            );
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getPurpose(string $rawToken): string
    {
        $parsed = explode(".", $rawToken);
        if (!isset($parsed[1]) || !\in_array($parsed[1], ['local', 'public'])) {
            throw new InvalidArgumentException("Invalid token");
        }
        return $parsed[1];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var string $rawToken
         */
        $rawToken = $input->getArgument("token");
        if (empty($rawToken)) {
            $output->writeln("A token is required");
            return Command::FAILURE;
        }
        try {
            $purpose = $this->getPurpose($rawToken);
        } catch (\InvalidArgumentException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
        $parser = ($purpose === 'local') ? $this->localParser : $this->publicParser;

        try {
            $parsedToken = $parser->parse($rawToken);
        } catch (\Exception $e) {
            $output->writeln("Invalid token");
            return Command::FAILURE;
        }

        if (!empty($parsedToken->getClaims())) {
            $output->writeln("Claims:");
            foreach ($parsedToken->getClaims() as $key => $val) {
                $output->writeln("{$key} => {$val}");
            }
        }

        if ($parsedToken->getFooter() !== "") {
            $output->writeln("Footer:");
            foreach ($parsedToken->getFooterArray() as $key => $val) {
                $output->writeln("{$key} => {$val}");
            }
        }
        return Command::SUCCESS;
    }
}
