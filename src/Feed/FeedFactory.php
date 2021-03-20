<?php

namespace Markocupic\RssFeedGeneratorBundle\Feed;


class FeedFactory
{

    /**
     * @var Feed
     */
    private $feed;

    /**
     * @var FeedItem
     */
    private $feedItem;

    /**
     * Channel constructor.
     */
    public function __construct(Feed $feed, FeedItem $feedItem)
    {
        $this->feed = $feed;
        $this->feedItem = $feedItem;
    }

    public function createFeed(string $encoding): Feed
    {
        return $this->feed->create($encoding);
    }

    public function createFeedItem(): FeedItem
    {
        return $this->feedItem->create();
    }

}