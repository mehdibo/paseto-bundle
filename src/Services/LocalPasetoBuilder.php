<?php


namespace Mehdibo\Bundle\PasetoBundle\Services;


use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\JsonToken;
use ParagonIE\Paseto\ProtocolInterface;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\SendingKey;

class LocalPasetoBuilder extends Builder
{

    public function __construct(JsonToken $baseToken = null, ProtocolInterface $protocol = null, SendingKey $key = null)
    {
        parent::__construct($baseToken, $protocol, $key);
        parent::setPurpose(Purpose::local());
    }

}