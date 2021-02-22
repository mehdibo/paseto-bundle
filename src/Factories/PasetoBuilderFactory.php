<?php

namespace Mehdibo\Bundle\PasetoBundle\Factories;

use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoBuilder;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoBuilder;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\SymmetricKey;

class PasetoBuilderFactory
{

    public static function localPasetoFactory(SymmetricKey $symmetricKey): LocalPasetoBuilder
    {
        return (new LocalPasetoBuilder())->setKey($symmetricKey, true);
    }

    public static function publicPasetoFactory(AsymmetricSecretKey $asymmetricKey): PublicPasetoBuilder
    {
        return (new PublicPasetoBuilder())->setKey($asymmetricKey, true);
    }
}