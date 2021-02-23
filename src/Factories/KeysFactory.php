<?php


namespace Mehdibo\Bundle\PasetoBundle\Factories;


use ParagonIE\Paseto\Keys\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\SymmetricKey;

class KeysFactory
{

    public static function symmetricKeyFactory(string $key): SymmetricKey
    {
        return new SymmetricKey($key);
    }

    public static function asymmetricSecretKeyFactory(string $key): AsymmetricSecretKey
    {
        return new AsymmetricSecretKey($key);
    }

    public static function asymmetricPublicKeyFactory(AsymmetricSecretKey $asymmetricSecretKey): AsymmetricPublicKey
    {
        return $asymmetricSecretKey->getPublicKey();
    }

}