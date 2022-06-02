<?php

declare(strict_types=1);

/*
 * This file is part of RSS Feed Generator Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rss-feed-generator-bundle
 */

namespace Markocupic\RssFeedGeneratorBundle\Feed;

use Markocupic\RssFeedGeneratorBundle\Formatter\Formatter;

class FeedFactory
{
    private Formatter $formatter;

    /**
     * FeedFactory constructor.
     */
    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public function createFeed(string $encoding): Feed
    {
        return new Feed($this->formatter, $encoding);
    }
}
