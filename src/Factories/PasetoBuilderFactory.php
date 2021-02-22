<?php

namespace Mehdibo\Bundle\PasetoBundle\Factories;

use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoBuilder;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoBuilder;
use ParagonIE\Paseto\Keys\Version2\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\Version2\SymmetricKey;

class PasetoBuilderFactory
{

    public static function localPasetoFactory(string $symmetricKey): LocalPasetoBuilder
    {
        $key = new SymmetricKey($symmetricKey);
        return (new LocalPasetoBuilder())->setKey($key, true);
    }

    public static function publicPasetoFactory(string $asymmetricKey): PublicPasetoBuilder
    {
        $key = new AsymmetricSecretKey($asymmetricKey);
        return (new PublicPasetoBuilder())->setKey($key, true);
    }
}