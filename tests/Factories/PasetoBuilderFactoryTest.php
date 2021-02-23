<?php

namespace Mehdibo\Bundle\PasetoBundle\Tests\Factories;

use Mehdibo\Bundle\PasetoBundle\Factories\PasetoBuilderFactory;
use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoBuilder;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoBuilder;
use ParagonIE\Paseto\Keys\Version2\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\Version2\SymmetricKey;
use ParagonIE\Paseto\Parser;
use PHPUnit\Framework\TestCase;

class PasetoBuilderFactoryTest extends TestCase
{

    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    public function testLocalPasetoFactory(): void
    {
        $claims = [
            'drink_milk' => 'stronk_bonks'
        ];

        $symmetricKey = new SymmetricKey(\random_bytes(32));
        $builder = PasetoBuilderFactory::localPasetoFactory($symmetricKey);
        $this->assertInstanceOf(LocalPasetoBuilder::class, $builder);

        $token = $builder->setClaims($claims)->toString();

        $parsedToken = $this->parser->setKey($symmetricKey)->parse($token);

        $this->assertEquals($claims, $parsedToken->getClaims());
    }

    public function testPublicPasetoFactory(): void
    {
        $claims = [
            'drink_milk' => 'stronk_bonks'
        ];

        $secretKey = new AsymmetricSecretKey(\sodium_crypto_sign_keypair());
        $builder = PasetoBuilderFactory::publicPasetoFactory($secretKey);
        $this->assertInstanceOf(PublicPasetoBuilder::class, $builder);

        $token = $builder->setClaims($claims)->toString();

        $parsedToken = $this->parser->setKey($secretKey->getPublicKey())->parse($token);

        $this->assertEquals($claims, $parsedToken->getClaims());
    }
}