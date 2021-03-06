<?php

namespace Bundles\PasswordLoginBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PasswordLoginExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $container->setParameter('password_login.config', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('command.yml');
        $loader->load('controller.yml');
        $loader->load('forms.yml');
        $loader->load('listener.yml');
        $loader->load('manager.yml');
        $loader->load('repository.yml');
        $loader->load('service.yml');
    }
}
