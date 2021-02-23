<?php


namespace Mehdibo\Bundle\PasetoBundle\Tests\Factories;


use Mehdibo\Bundle\PasetoBundle\Factories\ParserFactory;
use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoParser;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoParser;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\Version2\SymmetricKey;
use ParagonIE\Paseto\Purpose;
use PHPUnit\Framework\TestCase;

class ParserFactoryTest extends TestCase
{

    public function testLocalParserFactory(): void
    {
        // Create symmetric key
        $symmetricKey = new SymmetricKey(\random_bytes(32));

        // Create a test token
        $claims = [
            'drink_milk' => 'stronk_bonks'
        ];
        $testToken = (new Builder())
            ->setPurpose(Purpose::local())
            ->setKey($symmetricKey)
            ->setClaims($claims)
            ->toString();

        // Use factory
        $parser = ParserFactory::localParser($symmetricKey);
        $this->assertInstanceOf(LocalPasetoParser::class, $parser);

        // Parse test token
        $parsedToken = $parser->parse($testToken);

        $this->assertEquals($claims, $parsedToken->getClaims());
    }

    public function testPublicParserFactory(): void
    {
        // Create symmetric key
        $asymmetricSecretKey = new AsymmetricSecretKey(\sodium_crypto_sign_keypair());

        // Create a test token
        $claims = [
            'drink_milk' => 'stronk_bonks'
        ];
        $testToken = (new Builder())
            ->setPurpose(Purpose::public())
            ->setKey($asymmetricSecretKey)
            ->setClaims($claims)
            ->toString();

        // Use factory
        $parser = ParserFactory::publicParser($asymmetricSecretKey->getPublicKey());
        $this->assertInstanceOf(PublicPasetoParser::class, $parser);

        // Parse test token
        $parsedToken = $parser->parse($testToken);

        $this->assertEquals($claims, $parsedToken->getClaims());
    }

}