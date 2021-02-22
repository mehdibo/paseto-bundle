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
        $key = new SymmetricKey(random_bytes(32));
        $builder = PasetoBuilderFactory::localPasetoFactory($key)->setClaims(['test' => 'value']);
        $this->assertInstanceOf(LocalPasetoBuilder::class, $builder);

        $rawToken = $builder->toString();

        $this->parser->setKey($key);

        $token = $this->parser->parse($rawToken);
        $this->assertEquals(['test' => 'value'], $token->getClaims());
    }

    public function testPublicPasetoFactory(): void
    {
        $key = new AsymmetricSecretKey(sodium_crypto_sign_keypair());
        $builder = PasetoBuilderFactory::publicPasetoFactory($key)->setClaims(['test' => 'value']);
        $this->assertInstanceOf(PublicPasetoBuilder::class, $builder);

        $rawToken = $builder->toString();

        $this->parser->setKey($key->getPublicKey());

        $token = $this->parser->parse($rawToken);
        $this->assertEquals(['test' => 'value'], $token->getClaims());
    }
}