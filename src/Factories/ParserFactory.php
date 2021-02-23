<?php


namespace Mehdibo\Bundle\PasetoBundle\Factories;

use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoParser;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoParser;
use ParagonIE\Paseto\Keys\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Purpose;

class ParserFactory
{

    public static function localParser(SymmetricKey $symmetricKey): LocalPasetoParser
    {
        $parser = new LocalPasetoParser();
        $parser->setKey($symmetricKey)
            ->setPurpose(Purpose::local());
        return $parser;
    }

    public static function publicParser(AsymmetricPublicKey $publicKey): PublicPasetoParser
    {
        $parser = new PublicPasetoParser();
        $parser->setKey($publicKey)
            ->setPurpose(Purpose::public());
        return $parser;
    }
}
