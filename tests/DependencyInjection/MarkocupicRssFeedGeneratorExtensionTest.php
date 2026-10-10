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

namespace Markocupic\RssFeedGeneratorBundle\Tests\DependencyInjection;

use Markocupic\RssFeedGeneratorBundle\DependencyInjection\MarkocupicRssFeedGeneratorExtension;
use Markocupic\RssFeedGeneratorBundle\Feed\FeedFactory;
use Markocupic\RssFeedGeneratorBundle\Formatter\Formatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MarkocupicRssFeedGeneratorExtensionTest extends TestCase
{
    public function testRegistersTheServicesWithTheDefaultFilter(): void
    {
        $container = new ContainerBuilder();

        (new MarkocupicRssFeedGeneratorExtension())->load([], $container);

        $this->assertTrue($container->hasDefinition(FeedFactory::class));
        $this->assertTrue($container->hasDefinition(Formatter::class));
        $this->assertSame('&lt;', $container->getParameter('markocupic_rss_feed_generator.filter')['/</']);
    }

    public function testOverridesTheFilter(): void
    {
        $container = new ContainerBuilder();

        (new MarkocupicRssFeedGeneratorExtension())->load([['filter' => ['/foo/' => 'bar']]], $container);

        $this->assertSame(['/foo/' => 'bar'], $container->getParameter('markocupic_rss_feed_generator.filter'));
    }

    public function testTheFeedFactoryCanBeCompiled(): void
    {
        $container = new ContainerBuilder();

        (new MarkocupicRssFeedGeneratorExtension())->load([], $container);

        $container->getDefinition(FeedFactory::class)->setPublic(true);
        $container->compile();

        $this->assertInstanceOf(FeedFactory::class, $container->get(FeedFactory::class));
    }
}
