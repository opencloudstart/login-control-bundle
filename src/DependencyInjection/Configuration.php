<?php

namespace LoginControl\src\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package LoginControl\DependencyInjection
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder =  new TreeBuilder('login_control');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->children()
            ->booleanNode('services')
            ->defaultFalse()
            ->end()
        ->end();

        return $treeBuilder;


    }
}
