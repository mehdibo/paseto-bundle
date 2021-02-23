<?php


namespace Mehdibo\Bundle\PasetoBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class MehdiboPasetoExtension extends Extension
{

    /**
     * @param array<string, mixed> $configs
     */
    private function loadConfigs(string $prefix, array $configs, ContainerBuilder $container): void
    {
        foreach ($configs as $key => $val)
        {
            if (\is_array($val)) {
                $this->loadConfigs($prefix.'.'.$key, $val, $container);
                continue;
            }
            $container->setParameter($prefix.'.'.$key, $val);
        }
    }

    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $this->loadConfigs('mehdibo_paseto', $config, $container);
    }
}