<?php


namespace Mehdibo\Bundle\PasetoBundle\Tests\Factories;


use Mehdibo\Bundle\PasetoBundle\Factories\KeysFactory;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Keys\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Purpose;
use PHPUnit\Framework\TestCase;

class KeysFactoryTest extends TestCase
{

    private Parser $parser;
    private Builder $builder;

    protected function setUp(): void
    {
        $this->parser = new Parser();
        $this->builder = new Builder();
    }

    public function testSymmetricKeyFactory(): void
    {
        $key = random_bytes(32);
        $symmetricKey = KeysFactory::symmetricKeyFactory($key);

        $this->assertInstanceOf(SymmetricKey::class, $symmetricKey);

        $this->builder->setPurpose(Purpose::local())->setKey($symmetricKey);
        $this->builder->setClaims(['test' => 'value']);

        $rawToken = $this->builder->toString();

        $parsedToken = $this->parser->setKey($symmetricKey)->parse($rawToken);

        $this->assertEquals(['test' => 'value'], $parsedToken->getClaims());
    }

    public function testAsymmetricKeyFactory(): void
    {
        $key = random_bytes(32);

        $asymmetricSecretKey = KeysFactory::asymmetricSecretKeyFactory($key);
        $asymmetricPublicKey = KeysFactory::asymmetricPublicKeyFactory($key);

        $this->assertInstanceOf(AsymmetricSecretKey::class, $asymmetricSecretKey);
        $this->assertInstanceOf(AsymmetricPublicKey::class, $asymmetricPublicKey);

        $this->builder->setPurpose(Purpose::public())->setKey($asymmetricSecretKey);
        $this->builder->setClaims(['test' => 'value']);

        $rawToken = $this->builder->toString();

        $parsedToken = $this->parser->setKey($asymmetricPublicKey)->parse($rawToken);

        $this->assertEquals(['test' => 'value'], $parsedToken->getClaims());
    }

}