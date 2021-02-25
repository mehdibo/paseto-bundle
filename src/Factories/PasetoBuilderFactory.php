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
        $builder = new LocalPasetoBuilder();
        $builder->setKey($symmetricKey, true);
        return $builder;
    }

    public static function publicPasetoFactory(AsymmetricSecretKey $asymmetricKey): PublicPasetoBuilder
    {
        $builder = new PublicPasetoBuilder();
        $builder->setKey($asymmetricKey);
        return $builder;
    }
}
