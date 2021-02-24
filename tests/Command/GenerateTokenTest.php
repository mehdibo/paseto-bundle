<?php


namespace Mehdibo\Bundle\PasetoBundle\Tests\Command;

use Mehdibo\Bundle\PasetoBundle\Command\GenerateToken;
use Mehdibo\Bundle\PasetoBundle\Factories\KeysFactory;
use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoBuilder;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoBuilder;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Purpose;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateTokenTest extends TestCase
{
    private SymmetricKey $symmetricKey;
    private AsymmetricSecretKey $asymmetricSecretKey;
    private CommandTester $commandTester;

    /**
     * @var array<string , mixed>
     */
    private array $defaultExecOpts = ['interactive' => false];

    protected function setUp(): void
    {
        $this->symmetricKey = KeysFactory::symmetricKeyFactory(\random_bytes(32));
        $this->asymmetricSecretKey = KeysFactory::asymmetricSecretKeyFactory(\sodium_crypto_sign_keypair());

        $localBuilder = new LocalPasetoBuilder();
        $localBuilder->setKey($this->symmetricKey);

        $publicBuilder = new PublicPasetoBuilder();
        $publicBuilder->setKey($this->asymmetricSecretKey);

        $command = new GenerateToken($localBuilder, $publicBuilder);
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandGeneratesToken(): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $execStatus = $this->commandTester->execute([], $execOptions);

        $this->assertEquals(Command::SUCCESS, $execStatus);
        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEmpty($output[1]);

        $parsedToken = $this->getParser('local')->parse($output[0]);

        $this->assertEmpty($parsedToken->getClaims());
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithPurpose(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--purpose" => $purpose,
        ];
        $execStatus = $this->commandTester->execute($options, $execOptions);
        $this->assertEquals(Command::SUCCESS, $execStatus);

        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEmpty($output[1]);

        $parsedToken = $this->getParser($purpose)->parse($output[0]);

        $this->assertEmpty($parsedToken->getClaims());
    }

    public function testCommandGeneratesTokenWithInvalidPurpose(): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--purpose" => "invalid_purpose"
        ];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Purpose invalid, expected 'public' or 'local', found 'invalid_purpose'");
        $this->commandTester->execute($options, $execOptions);
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithAudience(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--aud" => "audience_goes_here",
            "--purpose" => $purpose,
        ];
        $execStatus = $this->commandTester->execute($options, $execOptions);
        $this->assertEquals(Command::SUCCESS, $execStatus);

        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEmpty($output[1]);

        $parsedToken = $this->getParser($purpose)->parse($output[0]);

        $this->assertEquals(['aud' => 'audience_goes_here'], $parsedToken->getClaims());
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithExpiration(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--expires_at" => "P01D",
            "--purpose" => $purpose,
        ];
        $execStatus = $this->commandTester->execute($options, $execOptions);
        $this->assertEquals(Command::SUCCESS, $execStatus);

        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEmpty($output[1]);

        $parsedToken = $this->getParser($purpose)->parse($output[0]);

        $this->assertArrayHasKey('exp', $parsedToken->getClaims());
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithIssuedAt(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--issued_at" => "2021-03-23 13:37:00",
            "--purpose" => $purpose,
        ];
        $execStatus = $this->commandTester->execute($options, $execOptions);
        $this->assertEquals(Command::SUCCESS, $execStatus);

        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEmpty($output[1]);

        $parsedToken = $this->getParser($purpose)->parse($output[0]);

        $this->assertEquals(["iat" => "2021-03-23T13:37:00+00:00"], $parsedToken->getClaims());
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithIssuer(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--issuer" => "issuer_here",
            "--purpose" => $purpose,
        ];
        $execStatus = $this->commandTester->execute($options, $execOptions);
        $this->assertEquals(Command::SUCCESS, $execStatus);

        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEmpty($output[1]);

        $parsedToken = $this->getParser($purpose)->parse($output[0]);

        $this->assertEquals(["iss" => "issuer_here"], $parsedToken->getClaims());
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithJti(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--jti" => "jti_here",
            "--purpose" => $purpose,
        ];
        $execStatus = $this->commandTester->execute($options, $execOptions);
        $this->assertEquals(Command::SUCCESS, $execStatus);

        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEmpty($output[1]);

        $parsedToken = $this->getParser($purpose)->parse($output[0]);

        $this->assertEquals(["jti" => "jti_here"], $parsedToken->getClaims());
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithClaims(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--claim" => ["key", "val", "key2", "val2"],
            "--purpose" => $purpose,
        ];
        $execStatus = $this->commandTester->execute($options, $execOptions);
        $this->assertEquals(Command::SUCCESS, $execStatus);

        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEmpty($output[1]);

        $parsedToken = $this->getParser($purpose)->parse($output[0]);

        $this->assertEquals(["key" => "val", "key2" => "val2"], $parsedToken->getClaims());
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithOddClaims(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--claim" => ["key", "val", "key2"],
            "--purpose" => $purpose,
        ];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Invalid claims, number of flags must be a pair, 3 given");
        $this->commandTester->execute($options, $execOptions);
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithFooter(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--footer" => ["key", "val", "key2", "val2"],
            "--purpose" => $purpose,
        ];
        $execStatus = $this->commandTester->execute($options, $execOptions);
        $this->assertEquals(Command::SUCCESS, $execStatus);

        $output = explode("\n", $this->commandTester->getDisplay());
        $this->assertCount(2, $output);
        $this->assertEmpty($output[1]);

        $parsedToken = $this->getParser($purpose)->parse($output[0]);

        $this->assertEquals(["key" => "val", "key2" => "val2"], $parsedToken->getFooterArray());
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testCommandGeneratesTokenWithOddFooter(string $purpose): void
    {
        $execOptions = \array_merge($this->defaultExecOpts, []);
        $options = [
            "--footer" => ["key", "val", "key2"],
            "--purpose" => $purpose,
        ];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Invalid footer, number of flags must be a pair, 3 given");
        $this->commandTester->execute($options, $execOptions);
    }

    /**
     * @dataProvider keyTypesDataProvider
     */
    public function testInteractWithCommand(string $purpose): void
    {
        $answers = [
            $purpose,
            'audience_here',
            'P01D',
            '2021-03-22 13:37:42',
            'spoody',
            '',
            'claim_a',
            'value_a',
            'claim_b',
            'value_b',
            '',
            'footer_a',
            'footer_b',
        ];
        $this->commandTester->setInputs($answers);
        $execStatus = $this->commandTester->execute([]);
        $this->assertEquals(Command::SUCCESS, $execStatus);
        $output = explode("Footer key: (Enter to skip)", $this->commandTester->getDisplay());
        $this->assertCount(3, $output);
        $parsedToken = $this->getParser($purpose)->parse(trim($output[2], "\n"));
        $claims = $parsedToken->getClaims();
        $this->assertArrayHasKey('exp', $claims);
        unset($claims['exp']);
        $this->assertEquals([
            'aud' => 'audience_here',
            'iat' => '2021-03-22T13:37:42+00:00',
            'iss' => 'spoody',
            'claim_a' => 'value_a',
            'claim_b' => 'value_b',
        ], $claims);
        $footer = $parsedToken->getFooterArray();
        $this->assertEquals(['footer_a' => 'footer_b'], $footer);
    }

    private function getParser(string $purpose): Parser
    {
        $parser = new Parser();
        $parser->setAllowedVersions(ProtocolCollection::v2())
            ->setPurpose(Purpose::{$purpose}())
            ->setKey(($purpose === "local") ? $this->symmetricKey : $this->asymmetricSecretKey->getPublicKey());
        return $parser;
    }

    /**
     * @return string[][]
     */
    public function keyTypesDataProvider(): array
    {
        return [
            'Local purpose' => ['local'],
            'Public purpose' => ['public'],
        ];
    }
}
