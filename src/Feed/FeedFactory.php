<?php

declare(strict_types=1);

/*
 * This file is part of RSS Feed GeneratorBundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rss-feed-generator-bundle
 */

namespace Markocupic\RssFeedGeneratorBundle\Feed;

class FeedFactory
{
    /**
     * @var Feed
     */
    private $feed;

    /**
     * FeedFactory constructor.
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    public function createFeed(string $encoding): Feed
    {
        return $this->feed->create($encoding);
    }
}
