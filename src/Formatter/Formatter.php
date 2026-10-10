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

namespace Markocupic\RssFeedGeneratorBundle\Formatter;

use Markocupic\RssFeedGeneratorBundle\Feed\Feed;
use Markocupic\RssFeedGeneratorBundle\Item\Item;
use Markocupic\RssFeedGeneratorBundle\Item\ItemGroup;
use Markocupic\RssFeedGeneratorBundle\Item\ItemInterface;

class Formatter
{
    private \DOMDocument $dom;

    /**
     * @param array<string, string> $arrFilter Regular expressions (search => replace)
     */
    public function __construct(private readonly array $arrFilter = [])
    {
    }

    public function render(Feed $feed): string
    {
        $this->dom = new \DOMDocument('1.0', $feed->getEncoding());

        if ($feed->getFormatNicely()) {
            $this->dom->preserveWhiteSpace = false;
            $this->dom->formatOutput = true;
        } else {
            $this->dom->preserveWhiteSpace = true;
            $this->dom->formatOutput = false;
        }

        // Add comment
        $this->dom->appendChild($this->dom->createComment('Generated with markocupic/rss-feed-generator-bundle. See https://github.com/markocupic/rss-feed-generator-bundle'));

        // Create the "rss" root element
        $rss = $this->addAttributes($this->dom->createElement('rss'), $feed->getRootAttributes());
        $rss->setAttribute('version', $feed->getVersion());
        $this->dom->appendChild($rss);

        // Create the "channel" element
        $channel = $this->addAttributes($this->dom->createElement('channel'), $feed->getChannelAttributes());
        $rss->appendChild($channel);

        // Add channel elements
        foreach ($feed->getChannelFields() as $objItem) {
            $this->appendElementIntoNode($objItem, $channel);
        }

        // Add channel items
        foreach ($feed->getChannelItemFields() as $item) {
            $this->addChannelItem($channel, $item);
        }

        return (string) $this->dom->saveXML();
    }

    private function addChannelItem(\DOMElement $channel, ItemInterface $objItem): void
    {
        // A single item (e.g. new Item('item', ...)) is appended as it is
        if (!$objItem instanceof ItemGroup) {
            $this->appendElementIntoNode($objItem, $channel);

            return;
        }

        $node = $this->dom->createElement('item');
        $channel->appendChild($node);

        foreach ($objItem->getChannelItemFields() as $element) {
            if ($element instanceof ItemInterface) {
                $this->appendElementIntoNode($element, $node);
            }
        }
    }

    private function appendElementIntoNode(ItemInterface $objItem, \DOMElement $node): \DOMElement|null
    {
        return match (true) {
            $objItem instanceof Item => $this->appendItemField($objItem, $node),
            $objItem instanceof ItemGroup => $this->appendItemGroupField($objItem, $node),
            default => null,
        };
    }

    private function appendItemField(Item $objItem, \DOMElement $parentNode): \DOMElement
    {
        $arrOptions = $objItem->getOptions();

        $newElement = $this->addAttributes($this->dom->createElement($objItem->getName()), $objItem->getAttributes());

        $strContent = (string) $objItem->getContent();

        if ('' !== $strContent) {
            // Filter
            $arrFilter = isset($arrOptions['filter']) && \is_array($arrOptions['filter']) ? $arrOptions['filter'] : $this->arrFilter;

            foreach ($arrFilter as $search => $replace) {
                $strContent = (string) preg_replace($search, (string) $replace, $strContent);
            }

            if ('' !== $strContent && !empty($arrOptions['cdata'])) {
                // Make cdata
                $newElement->appendChild($this->dom->createCDATASection($strContent));
            } else {
                $newElement->textContent = $strContent;
            }
        }

        $parentNode->appendChild($newElement);

        return $newElement;
    }

    private function appendItemGroupField(ItemGroup $objItemGroup, \DOMElement $parentNode): \DOMElement
    {
        $newElement = $this->addAttributes($this->dom->createElement($objItemGroup->getName()), $objItemGroup->getAttributes());

        foreach ($objItemGroup->getChannelItemFields() as $xmlSubElement) {
            if ($xmlSubElement instanceof ItemInterface) {
                $this->appendElementIntoNode($xmlSubElement, $newElement);
            }
        }

        $parentNode->appendChild($newElement);

        return $newElement;
    }

    private function addAttributes(\DOMElement $node, array $arrAttributes): \DOMElement
    {
        foreach ($arrAttributes as $attrName => $attrValue) {
            $node->setAttribute((string) $attrName, (string) $attrValue);
        }

        return $node;
    }
}
