<?php


namespace Mehdibo\Bundle\PasetoBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class MehdiboPasetoExtension extends Extension
{

    private function loadConfigs(string $prefix, array $configs, ContainerBuilder $container)
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

    public function load(array $configs, ContainerBuilder $container)
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