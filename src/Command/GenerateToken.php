<?php


namespace Mehdibo\Bundle\PasetoBundle\Command;

use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoBuilder;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoBuilder;
use ParagonIE\Paseto\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

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
            )->addOption(
                "claim",
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                "Custom claims, example --claim claim_key --claim claim_value",
                []
            )->addOption(
                "footer",
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                "Footer data, example --footer key --claim value",
                []
            );
    }

    private function createBuilder(InputInterface $input): Builder
    {
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

        $options = [
            'aud' => [
                'setter' => 'setAudience',
            ],
            'expires_at' => [
                'setter' => 'setExpiration',
                'prepValue' => function (string $optionValue) {
                    return (new \DateTime())->add(new \DateInterval($optionValue));
                }
            ],
            'issued_at' => [
                'setter' => 'setIssuedAt',
                'prepValue' => function (string $optionValue) {
                    return new \DateTime($optionValue);
                }
            ],
            'issuer' => [
                'setter' => 'setIssuer',
            ],
            'jti' => [
                'setter' => 'setJti',
            ],
        ];

        foreach ($options as $optionName => $params) {
            /**
             * @var string $optionValue
             */
            $optionValue = $input->getOption($optionName);
            if (isset($params['prepValue']) && \is_callable($params['prepValue']) && $optionValue !== "") {
                $optionValue = $params['prepValue']($optionValue);
            }
            if ($optionValue !== "") {
                $builder->{$params['setter']}($optionValue);
            }
        }

        /**
         * @var string[] $claims
         */
        $claims = (array) $input->getOption("claim");
        if (!empty($claims)) {
            $len = count($claims);
            if ($len % 2 !== 0) {
                throw new \RuntimeException(
                    sprintf("Invalid claims, number of flags must be a pair, %d given", $len)
                );
            }
            for ($i = 0; $i < $len; $i += 2) {
                $key = $claims[$i];
                $val = $claims[$i+1];
                $builder->set($key, $val);
            }
        }

        /**
         * @var string[] $footer
         */
        $footer = (array) $input->getOption("footer");
        $footerData = [];
        if (!empty($footer)) {
            $len = count($footer);
            if ($len % 2 !== 0) {
                throw new \RuntimeException(
                    sprintf("Invalid footer, number of flags must be a pair, %d given", $len)
                );
            }
            for ($i = 0; $i < $len; $i += 2) {
                $footerData[$footer[$i]] = $footer[$i+1];
            }
        }
        if (!empty($footerData)) {
            $builder->setFooterArray($footerData);
        }
        return $builder;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $builder = $this->createBuilder($input);

        $output->writeln($builder->toString());
        return 0;
    }

    /**
     * @return string[]
     */
    private function promptKeyPairs(
        QuestionHelper $helper,
        InputInterface $input,
        OutputInterface $output,
        string $keyPrompt,
        string $valPrompt
    ): array {
        $keyQuestion = new Question($keyPrompt, '');
        $valQuestion = new Question($valPrompt, '');
        $values = [];
        while (true) {
            $key = (string) $helper->ask($input, $output, $keyQuestion);
            if ($key === '') {
                break;
            }
            $val = (string) $helper->ask($input, $output, $valQuestion);
            if ($val === '') {
                break;
            }
            $values[] = $key;
            $values[] = $val;
        }
        return $values;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $prompts = [
            'purpose' => [
                'question_class' => ChoiceQuestion::class,
                'question' => 'Token purpose',
                'args' => [
                    ['local', 'public']
                ],
            ],
            'aud' => [
                'question_class' => Question::class,
                'question' => 'Audience claim: ',
                'args' => [
                    ''
                ],
            ],
            'expires_at' => [
                'question_class' => Question::class,
                'question' => 'Expires at: ',
                'args' => [
                    ''
                ],
            ],
            'issued_at' => [
                'question_class' => Question::class,
                'question' => 'Issued at: ',
                'args' => [
                    'now'
                ],
            ],
            'issuer' => [
                'question_class' => Question::class,
                'question' => 'Issuer: ',
                'args' => [
                    ''
                ],
            ],
            'jti' => [
                'question_class' => Question::class,
                'question' => 'JWT ID (JTI): ',
                'args' => [
                    ''
                ],
            ],
        ];
        $helper = new QuestionHelper();

        foreach ($prompts as $optionName => $prompt) {
            // @phpstan-ignore-next-line
            $question = new $prompt['question_class']($prompt['question'], ...$prompt['args']);
            if (isset($prompt['autocomplete'])) {
                $question->setAutocompleterValues($prompt['autocomplete']);
            }
            $value = $helper->ask($input, $output, $question);
            $input->setOption($optionName, $value);
        }

        // Add claims
        $claims = $this->promptKeyPairs(
            $helper,
            $input,
            $output,
            "Claim key: (Enter to skip)",
            "Claim value: "
        );
        $input->setOption('claim', $claims);

        // Add footer
        $footer = $this->promptKeyPairs(
            $helper,
            $input,
            $output,
            "Footer key: (Enter to skip)",
            "Footer value: "
        );
        $input->setOption('footer', $footer);
    }
}
