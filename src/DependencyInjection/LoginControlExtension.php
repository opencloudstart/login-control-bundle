<?php
declare(strict_types=1);

namespace LoginControl\src\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LoginControlExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $locator = new FileLocator(__DIR__ . '/../Resources/config/');
        $loader  = new YamlFileLoader($container, $locator);

        if ($config['services']) {
            $loader->load('services.yml');
        }
        $loader->load('message_handlers.yml');

    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function prepend(ContainerBuilder $container): void
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new YamlFileLoader($container, $locator);

        $container->setParameter('login_control_bundle.src_dir', __DIR__ . '/../');
        $container->setParameter('login_control_bundle.resource_dir', __DIR__ . '/../Resources/');

        $loader->load('doctrine_mappings.yml');
        $loader->load('twig.yml');
    }
}
