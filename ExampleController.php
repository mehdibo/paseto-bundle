<?php
namespace App\Controller;

use http\Client\Response;
use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoBuilder;
use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoParser;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoBuilder;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends AbstractController
{

    #[Route('/public/decode', name: 'public_decode')]
    public function publicDecode(PublicPasetoParser $parser): JsonResponse
    {
        $token = $parser->parse("PUBLIC_TOKEN_HERE");
        return new JsonResponse($token->getClaims());
    }

    #[Route('/local/decode', name: 'local_decode')]
    public function localDecode(LocalPasetoParser $parser): JsonResponse
    {
        $token = $parser->parse("LOCAL_TOKEN_HERE");
        return new JsonResponse($token->getClaims());
    }

    #[Route('/public', name: 'public')]
    public function public(PublicPasetoBuilder $builder): Response
    {
        $builder->setIssuedAt()->setClaims(['custom' => 'claim']);
        return new Response($builder->toString());
    }

    #[Route('/local', name: 'local')]
    public function local(LocalPasetoBuilder $builder): Response
    {
        $builder->setIssuedAt()->setClaims(['custom' => 'claim']);
        return new Response($builder->toString());
    }
}