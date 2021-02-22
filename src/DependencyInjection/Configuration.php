<?php

namespace Mehdibo\Bundle\PasetoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mehdibo_paseto');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->append($this->createKeysNode());

        return $treeBuilder;
    }

    private function createKeysNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('secret_keys');
        $node = $treeBuilder->getRootNode();

        $node->isRequired()
            ->children()
                ->scalarNode('symmetric_key')
                    ->info('A HEX encoded key used for local Paseto tokens')
                ->end()
                ->scalarNode('asymmetric_key')
                    ->info('A HEX encoded key used for public Paseto tokens')
                ->end()
            ->end();

        return $node;
    }
}