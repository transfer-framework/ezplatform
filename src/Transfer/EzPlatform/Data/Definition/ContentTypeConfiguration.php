<?php

namespace Transfer\EzPlatform\Data\Definition;

use eZ\Publish\API\Repository\Values\Content\Location;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ContentTypeConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     **/
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('contenttype');
        $node
            ->children()
                ->arrayNode('names')
                    ->normalizeKeys(false)
                    ->prototype('scalar')->end()
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($v) { return array('eng-GB' => $v); })
                    ->end()
                ->end()
                ->arrayNode('descriptions')
                    ->normalizeKeys(false)
                    ->prototype('scalar')->end()
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($v) { return array('eng-GB' => $v); })
                    ->end()
                ->end()
                ->scalarNode('main_language_code')
                    ->defaultValue('eng-GB')
                ->end()
                ->arrayNode('contenttype_groups')
                    ->prototype('scalar')->end()
                    ->defaultValue(array('Content'))
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($v) { return array(ucfirst($v)); })
                    ->end()
                ->end()
                ->scalarNode('name_schema')->end()
                ->scalarNode('url_alias_schema')->end()
                ->booleanNode('is_container')
                    ->defaultTrue()
                ->end()
                ->booleanNode('default_always_available')
                    ->defaultFalse()
                ->end()
                ->integerNode('default_sort_field')
                    ->defaultValue(Location::SORT_FIELD_NAME)
                    ->min(1)->max(12)
                ->end()
                ->integerNode('default_sort_order')
                    ->defaultValue(Location::SORT_ORDER_ASC)
                    ->min(0)->max(1)
                ->end()
                ->arrayNode('fields')
                    ->fixXmlConfig('name')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')
                                ->defaultValue('ezstring')
                            ->end()
                            ->integerNode('position')
                                ->defaultValue(10)
                            ->end()
                            ->arrayNode('names')
                                ->normalizeKeys(false)
                                ->beforeNormalization()
                                ->ifString()
                                    ->then(function ($v) { return array('eng-GB' => $v); })
                                ->end()
                                ->prototype('scalar')->end()

                            ->end()
                            ->arrayNode('descriptions')
                                ->normalizeKeys(false)
                                ->prototype('scalar')->end()
                                ->beforeNormalization()
                                ->ifString()
                                    ->then(function ($v) { return array('eng-GB' => $v); })
                                ->end()
                            ->end()
                            ->scalarNode('field_group')
                                ->defaultValue('content')
                            ->end()
                                ->scalarNode('default_value')
                                ->defaultValue(null)
                            ->end()
                            ->booleanNode('is_required')
                                ->defaultFalse()
                            ->end()
                            ->booleanNode('is_translatable')
                                ->defaultTrue()
                            ->end()
                            ->booleanNode('is_searchable')
                                ->defaultTrue()
                            ->end()
                            ->booleanNode('is_info_collector')
                                ->defaultFalse()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
