<?php


namespace Mehdibo\Bundle\PasetoBundle\Factories;

use ParagonIE\Paseto\Keys\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\SymmetricKey;

class KeysFactory
{

    public static function symmetricKeyFactory(string $hexKey): SymmetricKey
    {
        $key = \hex2bin($hexKey);
        return new SymmetricKey($key);
    }

    public static function asymmetricSecretKeyFactory(string $hexKey): AsymmetricSecretKey
    {
        $key = \hex2bin($hexKey);
        return new AsymmetricSecretKey($key);
    }

    public static function asymmetricPublicKeyFactory(AsymmetricSecretKey $asymmetricSecretKey): AsymmetricPublicKey
    {
        return $asymmetricSecretKey->getPublicKey();
    }
}
