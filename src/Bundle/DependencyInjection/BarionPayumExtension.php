<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class BarionPayumExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config'),
        );
        $loader->load('services.xml');

        $container->setParameter('barion_payum.pos_key', $config['pos_key']);
        $container->setParameter('barion_payum.sandbox', $config['sandbox']);
        $container->setParameter('barion_payum.currency', $config['currency']);
    }
}
