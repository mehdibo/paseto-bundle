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
        $claims = [
            'drink_milk' => 'stronk_bonks'
        ];

        $symmetricKey = KeysFactory::symmetricKeyFactory(\bin2hex(\random_bytes(32)));

        $this->assertInstanceOf(SymmetricKey::class, $symmetricKey);

        $token = $this->builder
            ->setPurpose(Purpose::local())
            ->setKey($symmetricKey)
            ->setClaims($claims)
            ->toString();

        $parsedToken = $this->parser->setKey($symmetricKey)->parse($token);

        $this->assertEquals($claims, $parsedToken->getClaims());
    }

    public function testAsymmetricSecretKeyFactory(): void
    {
        $claims = [
            'drink_milk' => 'stronk_bonks'
        ];

        $randomBytes = \sodium_crypto_sign_keypair();

        $asymmetricSecretKey = KeysFactory::asymmetricSecretKeyFactory(\bin2hex($randomBytes));

        $this->assertInstanceOf(AsymmetricSecretKey::class, $asymmetricSecretKey);

        $token = $this->builder
            ->setPurpose(Purpose::public())
            ->setKey($asymmetricSecretKey)
            ->setClaims($claims)
            ->toString();

        $parsedToken = $this->parser->setKey($asymmetricSecretKey->getPublicKey())->parse($token);

        $this->assertEquals($claims, $parsedToken->getClaims());
    }

    public function testAsymmetricPublicKeyFactory(): void
    {
        $claims = [
            'drink_milk' => 'stronk_bonks'
        ];

        $randomBytes = \sodium_crypto_sign_keypair();

        $asymmetricSecretKey = KeysFactory::asymmetricSecretKeyFactory(\bin2hex($randomBytes));
        $asymmetricPublicKey = KeysFactory::asymmetricPublicKeyFactory($asymmetricSecretKey);

        $this->assertInstanceOf(AsymmetricPublicKey::class, $asymmetricPublicKey);

        $token = $this->builder
            ->setPurpose(Purpose::public())
            ->setKey($asymmetricSecretKey)
            ->setClaims($claims)
            ->toString();

        $parsedToken = $this->parser->setKey($asymmetricPublicKey)->parse($token);

        $this->assertEquals($claims, $parsedToken->getClaims());
    }
}
