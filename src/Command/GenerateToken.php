<?php


namespace Mehdibo\Bundle\PasetoBundle\Command;

use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoBuilder;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoBuilder;
use ParagonIE\Paseto\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateToken extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = "mehdibo:paseto:generate-token";

    private LocalPasetoBuilder $localBuilder;
    private PublicPasetoBuilder $publicBuilder;

    public function __construct(
        LocalPasetoBuilder $localBuilder,
        PublicPasetoBuilder $publicBuilder,
        string $name = null
    ) {
        parent::__construct($name);
        $this->localBuilder = $localBuilder;
        $this->publicBuilder = $publicBuilder;
    }

    protected function configure(): void
    {
        $this->setDescription("Generate a Paseto token")
            ->setHelp(<<< HELP
This command allows you to generate Paseto tokens
HELP
            )
            ->addOption(
                "purpose",
                null,
                InputOption::VALUE_REQUIRED,
                "Paseto token purpose, options are: public or local",
                "local"
            )
            ->addOption(
                "aud",
                null,
                InputOption::VALUE_OPTIONAL,
                "Audience claim",
                ''
            )->addOption(
                "expires_at",
                null,
                InputOption::VALUE_OPTIONAL,
                "When the token should expire, format is ISO_8601 Durations (P01D for 1 day)",
                ''
            )->addOption(
                "issued_at",
                null,
                InputOption::VALUE_OPTIONAL,
                "Add issued at date",
                ''
            )->addOption(
                "issuer",
                null,
                InputOption::VALUE_OPTIONAL,
                "Add issuer",
                ''
            )->addOption(
                "jti",
                null,
                InputOption::VALUE_OPTIONAL,
                "Add JTI (JWT ID claim)",
                ''
            );
    }

    private function createBuilder(InputInterface $input): Builder
    {
        $builder = null;
        // Validate purpose
        /**
         * @var string $purpose
         */
        $purpose = $input->getOption("purpose");
        $purpose = $purpose === "" ? "None" : $purpose;
        if (!\in_array($purpose, ["public", "local"])) {
            throw new \RuntimeException(
                sprintf("Purpose invalid, expected 'public' or 'local', found '%s'", $purpose)
            );
        }
        /**
         * @var Builder $builder
         */
        $builder = ($purpose === "local") ? $this->localBuilder : $this->publicBuilder;

        /**
         * @var string $aud
         */
        $aud = $input->getOption('aud');
        if ($aud !== "") {
            $builder->setAudience($aud);
        }

        /**
         * @var string $expiresAtInterval
         */
        $expiresAtInterval = $input->getOption('expires_at');
        if ($expiresAtInterval !== '') {
            $builder->setExpiration((new \DateTime())->add(new \DateInterval($expiresAtInterval)));
        }

        /**
         * @var string $issuedAt
         */
        $issuedAt = $input->getOption('issued_at');
        if ($issuedAt !== '') {
            $builder->setIssuedAt(new \DateTime($issuedAt));
        }

        /**
         * @var string $issuer
         */
        $issuer = $input->getOption('issuer');
        if ($issuer !== '') {
            $builder->setIssuer($issuer);
        }

        /**
         * @var string $jti
         */
        $jti = $input->getOption('jti');
        if ($jti !== '') {
            $builder->setJti($jti);
        }

        return $builder;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $builder = $this->createBuilder($input);

        $output->writeln($builder->toString());
        return 0;
    }
}
