<?php

namespace Mehdibo\Bundle\PasetoBundle\DependencyInjection;

use ParagonIE\ConstantTime\Binary;
use ParagonIE\Paseto\Protocol\Version2;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mehdibo_paseto');
        /**
         * @var ArrayNodeDefinition $rootNode
         */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->append($this->createKeysNode());

        return $treeBuilder;
    }

    private function validateKey(string $paramKey, string $value): string {
        if (!\ctype_xdigit($value)) {
            throw new InvalidConfigurationException(
                "'{$paramKey}' must be a hex encoded key"
            );
        }
        $decoded = \hex2bin($value);
        if ($decoded === false) {
            throw new InvalidConfigurationException("Failed converting {$paramKey} to binary");
        }
        return $decoded;
    }

    private function createKeysNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('secret_keys');
        /**
         * @var NodeDefinition $node
         */
        $node = $treeBuilder->getRootNode();

        $node->isRequired()
            ->children()
                ->scalarNode('symmetric_key')
                    ->info('A HEX encoded key used for local Paseto tokens')
                    ->validate()
                        ->always(function ($value) {
                            $paramKey = 'mehdibo_paseto.secret_keys.symmetric_key';
                            $decoded = $this->validateKey($paramKey, $value);
                            $keyLen = Binary::safeStrlen($decoded);
                            if ($keyLen !== Version2::SYMMETRIC_KEY_BYTES) {
                                throw new InvalidConfigurationException(
                                    sprintf(
                                        "'%s' must be %d bytes long, %d found",
                                        $paramKey,
                                        Version2::SYMMETRIC_KEY_BYTES,
                                        $keyLen
                                    )
                                );
                            }
                            return $decoded;
                        })
                    ->end()
                ->end()
                ->scalarNode('asymmetric_key')
                    ->info('A HEX encoded key used for public Paseto tokens')
                    ->validate()
                    ->always(function ($value) {
                        $paramKey = 'mehdibo_paseto.secret_keys.asymmetric_key';
                        $decoded = $this->validateKey($paramKey, $value);
                        $keyLen = Binary::safeStrlen($decoded);
                        if ($keyLen !== \SODIUM_CRYPTO_SIGN_SECRETKEYBYTES && $keyLen !== SODIUM_CRYPTO_SIGN_SEEDBYTES) {
                            throw new InvalidConfigurationException(
                                sprintf(
                                    "'%s' must be %d or %d bytes long, %d found",
                                    $paramKey,
                                    \SODIUM_CRYPTO_SIGN_SEEDBYTES,
                                    \SODIUM_CRYPTO_SIGN_SECRETKEYBYTES,
                                    $keyLen
                                )
                            );
                        }
                        return $decoded;
                    })
                    ->end()
                ->end()
            ->end();
        return $node;
    }
}