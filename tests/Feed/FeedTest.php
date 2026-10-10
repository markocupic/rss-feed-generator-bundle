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

namespace Markocupic\RssFeedGeneratorBundle\Tests\Feed;

use Markocupic\RssFeedGeneratorBundle\Feed\Feed;
use Markocupic\RssFeedGeneratorBundle\Feed\FeedFactory;
use Markocupic\RssFeedGeneratorBundle\Formatter\Formatter;
use Markocupic\RssFeedGeneratorBundle\Item\Item;
use Markocupic\RssFeedGeneratorBundle\Item\ItemGroup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class FeedTest extends TestCase
{
    private const DEFAULT_FILTER = [
        '/</' => '&lt;',
        '/\[-\]/' => '',
        '/\&shy;/' => '',
        '/\[nbsp\]/' => ' ',
        '/&nbsp;/' => ' ',
    ];

    public function testRendersTheFeed(): void
    {
        $feed = (new FeedFactory(new Formatter(self::DEFAULT_FILTER)))->createFeed(Feed::ENCODING_UTF8);
        $feed->setRootAttributes(['xmlns:atom' => 'http://www.w3.org/2005/Atom']);
        $feed->setChannelAttributes(['foo' => 'bar']);
        $feed->addChannelField(new Item('title', 'Demo[nbsp]feed'));
        $feed->addChannelField(new Item('description', 'Check our news!', ['cdata' => true], ['lang' => 'en']));
        $feed->addChannelItemField(new ItemGroup('item', [
            new Item('title', 'Ferrari is my favourite car!', ['filter' => ['/Ferrari/' => 'Italia', '/car/' => 'country']]),
            new Item('link', 'https://example.com/?a=1&b=2'),
            new Item('position', '0'),
            new ItemGroup('nestedItems', [new Item('subitem', 'Some content')], ['foo' => 'bar']),
        ]));

        $response = $feed->render();

        $this->assertSame('application/rss+xml; charset=utf-8', $response->headers->get('Content-Type'));

        $xml = new \SimpleXMLElement((string) $response->getContent());

        $this->assertSame('2.0', (string) $xml['version']);
        $this->assertSame('bar', (string) $xml->channel['foo']);
        $this->assertSame('Demo feed', (string) $xml->channel->title);
        $this->assertSame('Check our news!', (string) $xml->channel->description);
        $this->assertSame('en', (string) $xml->channel->description['lang']);
        $this->assertStringContainsString('<![CDATA[Check our news!]]>', (string) $response->getContent());

        $this->assertCount(1, $xml->channel->item);
        $this->assertSame('Italia is my favourite country!', (string) $xml->channel->item->title);
        $this->assertSame('https://example.com/?a=1&b=2', (string) $xml->channel->item->link);
        $this->assertSame('0', (string) $xml->channel->item->position);
        $this->assertSame('bar', (string) $xml->channel->item->nestedItems['foo']);
        $this->assertSame('Some content', (string) $xml->channel->item->nestedItems->subitem);
    }

    public function testSavesTheFeedToTheFilesystem(): void
    {
        $dir = sys_get_temp_dir().'/rss-feed-generator-test-'.bin2hex(random_bytes(4));
        $path = $dir.'/share/feed.xml';

        try {
            $feed = new Feed(new Formatter(), Feed::ENCODING_UTF8);
            $feed->addChannelField(new Item('title', 'Demo'));

            $response = $feed->render($path);

            $this->assertFileExists($path);
            $this->assertSame((string) $response->getContent(), file_get_contents($path));
            $this->assertSame('application/rss+xml; charset=utf-8; filename=feed.xml', $response->headers->get('Content-Type'));
        } finally {
            (new Filesystem())->remove($dir);
        }
    }

    public function testCanDisableFormatting(): void
    {
        $feed = (new Feed(new Formatter()))->doFormatNicely(false);
        $feed->addChannelField(new Item('title', 'Demo'));

        $this->assertStringContainsString('<rss version="2.0"><channel><title>Demo</title></channel></rss>', (string) $feed->render()->getContent());
    }
}
