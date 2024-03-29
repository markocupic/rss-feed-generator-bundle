<?php

declare(strict_types=1);

/*
 * This file is part of RSS Feed Generator Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rss-feed-generator-bundle
 */

namespace Markocupic\RssFeedGeneratorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROOT_KEY = 'markocupic_rss_feed_generator';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_KEY);

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('filter')
                    ->prototype('scalar')->end()
                    ->useAttributeAsKey('name')
                    ->defaultValue([
                        '/</' => '&lt;',
                        '/\[-\]/' => '',
                        '/\&shy;/' => '',
                        '/\[nbsp\]/' => ' ',
                        '/&nbsp;/' => ' ',
                    ])
                ->end()
        ;

        return $treeBuilder;
    }
}
