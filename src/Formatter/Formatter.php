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

namespace Markocupic\RssFeedGeneratorBundle\Formatter;

use Markocupic\RssFeedGeneratorBundle\Feed\Feed;
use Markocupic\RssFeedGeneratorBundle\Item\Item;
use Markocupic\RssFeedGeneratorBundle\Item\ItemGroup;
use Markocupic\RssFeedGeneratorBundle\Item\ItemInterface;

class Formatter
{
    /**
     * @var array
     */
    private $arrFilter;

    /**
     * @var Feed
     */
    private $feed;

    /**
     * @var \DOMDocument
     */
    private $dom;

    /**
     * Formatter constructor.
     */
    public function __construct(array $arrFilter)
    {
        $this->arrFilter = $arrFilter;
    }

    public function render(Feed $feed): string
    {
        $this->feed = $feed;

        $this->dom = new \DOMDocument('1.0', $this->feed->getEncoding());

        // Add comment
        $comment = $this->dom->createComment('Generated with markocupic/rss-feed-generator-bundle. See https://github.com/markocupic/rss-feed-generator-bundle');
        $this->dom->appendChild($comment);

        // Create element "rss"
        $rssEl = $this->dom->createElement('rss');

        // Append attributes
        $rssEl = $this->addAttributes($rssEl, $this->feed->getRootAttributes());

        // Append element rss to DOM
        $rss = $this->dom->appendChild($rssEl);

        // Add version
        $rss->setAttribute('version', $this->feed->getVersion());

        // Create element "channel"
        $channelEl = $this->dom->createElement('channel');

        // Append attributes
        $channelEl = $this->addAttributes($channelEl, $this->feed->getChannelAttributes());

        // Append channel to DOM
        $channel = $rss->appendChild($channelEl);

        if ($this->feed->getFormatNicely()) {
            $this->dom->preserveWhiteSpace = false;
            $this->dom->formatOutput = true;
        } else {
            $this->dom->preserveWhiteSpace = true;
            $this->dom->formatOutput = false;
        }

        // Add channel elements
        $arrChannelElements = $this->feed->getChannelFields();

        /** @var \ItemInterface $objItem */
        foreach ($arrChannelElements as $objItem) {
            $this->appendElementIntoNode($objItem, $channel);
        }

        // Add channel items
        foreach ($this->feed->getChannelItemFields() as $item) {
            $this->addChannelItem($channel, $item);
        }

        return $this->dom->saveXML();
    }

    private function addChannelItem(\DOMElement $channel, ItemInterface $objItemGroup): void
    {
        $node = $this->dom->createElement('item');
        $node = $channel->appendChild($node);
        $itemElements = $objItemGroup->getChannelItemFields();

        foreach ($itemElements as $element) {
            if ($element) {
                $this->appendElementIntoNode($element, $node);
            }
        }
    }

    private function appendElementIntoNode(ItemInterface $objItem, \DomElement $node)
    {
        $className = \get_class($objItem);

        switch ($className) {
            case Item::class:
                return $this->appendItemField($objItem, $node);
                break;

            case ItemGroup::class:
                return $this->appendItemGroupField($objItem, $node);
                break;
        }
    }

    private function appendItemField(Item $objItem, \DOMElement $parentNode): \DOMElement
    {
        $arrOptions = $objItem->getOptions();
        $newElement = $this->dom->createElement($objItem->getName());

        // Add attributes
        $this->addAttributes($newElement, $objItem->getAttributes());

        if ($objItem->getContent()) {
            $strContent = $objItem->getContent();

            // Filter
            $arrFilter = $this->arrFilter;

            if (isset($arrOptions['filter']) && \is_array($arrOptions['filter'])) {
                $arrFilter = $arrOptions['filter'];
            }

            foreach ($arrFilter as $search => $replace) {
                $strContent = preg_replace($search, $replace, $strContent);
            }

            // Make cdata
            if (null !== $strContent && \is_string($strContent) && \strlen($strContent) && isset($arrOptions['cdata']) && $arrOptions['cdata']) {
                $elementCdata = $this->dom->createCDATASection($strContent);
                $newElement->appendChild($elementCdata);
            } else {
                $newElement->textContent = $strContent;
            }
        }

        return $parentNode->appendChild($newElement);
    }

    private function appendItemGroupField(ItemGroup $objItemGroup, \DOMElement $parentNode): \DOMElement
    {
        $newElement = $this->dom->createElement($objItemGroup->getName());

        // Add attributes
        $this->addAttributes($newElement, $objItemGroup->getAttributes());

        $items = $objItemGroup->getChannelItemFields();

        foreach ($items as $xmlSubElement) {
            $className = \get_class($xmlSubElement);

            switch ($className) {
                // Element is a single field
                case Item::class:
                    $this->appendItemField($xmlSubElement, $newElement);
                    break;

                // Element is a group item
                case ItemGroup::class:
                    $this->appendItemGroupField($xmlSubElement, $newElement);
                    break;
            }
        }

        return $parentNode->appendChild($newElement);
    }

    private function addAttributes(\DomElement $node, array $arrAttributes): \DOMElement
    {
        foreach ($arrAttributes as $attrName => $attrValue) {
            //$attribute = $this->dom->createAttribute($attrName);
            //$attribute->value = $attrValue;
            //$elements[] = $node->appendChild($attribute);
            $node->setAttribute($attrName, $attrValue);
        }

        return $node;
    }
}
