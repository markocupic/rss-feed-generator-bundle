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

namespace Markocupic\RssFeedGeneratorBundle\Tests\Item;

use Markocupic\RssFeedGeneratorBundle\Item\Item;
use Markocupic\RssFeedGeneratorBundle\Item\ItemGroup;
use PHPUnit\Framework\TestCase;

class ItemGroupTest extends TestCase
{
    public function testAcceptsASingleItem(): void
    {
        $item = new Item('title', 'Foo');
        $group = new ItemGroup('item', $item, ['foo' => 'bar']);

        $this->assertSame('item', $group->getName());
        $this->assertSame([$item], $group->getChannelItemFields());
        $this->assertSame(['foo' => 'bar'], $group->getAttributes());
    }

    public function testRejectsInvalidItems(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ItemGroup('item', ['foo']);
    }
}
