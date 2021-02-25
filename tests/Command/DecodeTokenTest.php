<?php


namespace Command;

use Mehdibo\Bundle\PasetoBundle\Command\DecodeToken;
use Mehdibo\Bundle\PasetoBundle\Factories\KeysFactory;
use Mehdibo\Bundle\PasetoBundle\Factories\PasetoBuilderFactory;
use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoParser;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoParser;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\SymmetricKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DecodeTokenTest extends TestCase
{
    private SymmetricKey $symmetricKey;
    private AsymmetricSecretKey $asymmetricSecretKey;
    private CommandTester $commandTester;


    protected function setUp(): void
    {
        $this->symmetricKey = KeysFactory::symmetricKeyFactory(\bin2hex(\random_bytes(32)));
        $this->asymmetricSecretKey = KeysFactory::asymmetricSecretKeyFactory(\bin2hex(\sodium_crypto_sign_keypair()));

        $localParser = new LocalPasetoParser();
        $localParser->setKey($this->symmetricKey);

        $publicParser = new PublicPasetoParser();
        $publicParser->setKey($this->asymmetricSecretKey->getPublicKey());

        $command = new DecodeToken($localParser, $publicParser);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @param array<string, mixed> $claims
     * @param array<string, mixed> $footer
     */
    private function generateToken(string $purpose, array $claims = [], array $footer = []): string
    {
        $builder = ($purpose === 'local') ?
            PasetoBuilderFactory::localPasetoFactory($this->symmetricKey) :
            PasetoBuilderFactory::publicPasetoFactory($this->asymmetricSecretKey);
        $builder->setClaims($claims);
        if (!empty($footer)) {
            $builder->setFooterArray($footer);
        }
        return $builder->toString();
    }

    /**
     * @dataProvider tokensDataProvider
     * @param array<string, mixed> $claims
     * @param array<string, mixed> $footer
     */
    public function testDecodeToken(
        string $purpose,
        string $expectedOutput,
        array $claims = [],
        array $footer = []
    ): void {
        $token = $this->generateToken($purpose, $claims, $footer);
        $execStatus = $this->commandTester->execute(['token' => $token]);
        $this->assertEquals(Command::SUCCESS, $execStatus);
        $this->assertEquals($expectedOutput, $this->commandTester->getDisplay());
    }

    /**
     * @return array<string, array<int, array<string, string>|string>>
     */
    public function tokensDataProvider(): array
    {
        $purposes = ['local', 'public'];
        $tests = [
            'claims only' => [
                "Claims:\nclaim_a => value_a\n",
                ['claim_a' => 'value_a'],
                []
            ],
            'claims and footer' => [
                "Claims:\nclaim_a => value_a\nFooter:\nfooter_a => value_a\n",
                ['claim_a' => 'value_a'],
                ['footer_a' => 'value_a']
            ],
            'footer only' => [
                "Footer:\nfooter_a => value_a\n",
                [],
                ['footer_a' => 'value_a']
            ],
            'no claims and no footer' => [
                '',
                [],
                []
            ],
        ];
        $testCases = [];
        foreach ($purposes as $purpose) {
            foreach ($tests as $testName => $testArgs) {
                $testCases[$purpose.' purpose & '.$testName] = array_merge([$purpose], $testArgs);
            }
        }


        return $testCases;
    }
}
