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

namespace Markocupic\RssFeedGeneratorBundle\Formatter;

use Markocupic\RssFeedGeneratorBundle\Feed\Feed;
use Markocupic\RssFeedGeneratorBundle\XmlElement\XmlElement;
use Markocupic\RssFeedGeneratorBundle\XmlElement\XmlElementGroup;
use Markocupic\RssFeedGeneratorBundle\XmlElement\XmlElementInterface;

class Formatter
{
    private $arrFilter;

    /**
     * @var Feed
     */
    private $feed;

    /**
     * @var \DOMDocument
     */
    private $dom;

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
        $rss = $this->dom->appendChild($this->dom->createElement('rss'));
        $rss->setAttribute('version', $this->feed->getVersion());

        // Create element "channel"
        $channel = $rss->appendChild($this->dom->createElement('channel'));

        if ($this->feed->getFormatNicely()) {
            $this->dom->preserveWhiteSpace = false;
            $this->dom->formatOutput = true;
        } else {
            $this->dom->preserveWhiteSpace = true;
            $this->dom->formatOutput = false;
        }

        // Add channel elements
        $arrChannelElements = $this->feed->getChannelFields();

        /** @var \XmlElementInterface $xmlElement */
        foreach ($arrChannelElements as $xmlElement) {
            $this->appendElementIntoNode($xmlElement, $channel);
        }

        // Add channel items
        foreach ($this->feed->getItemFields() as $item) {
            $this->addChannelItem($channel, $item);
        }

        return $this->dom->saveXML();
    }

    private function addChannelItem(\DOMElement $channel, XmlElementInterface $xmlElementGroup): void
    {
        $node = $this->dom->createElement('item');
        $node = $channel->appendChild($node);
        $itemElements = $xmlElementGroup->getItemFields();

        foreach ($itemElements as $element) {
            $this->appendElementIntoNode($element, $node);
        }
    }

    private function appendElementIntoNode(XmlElementInterface $xmlElement, \DomElement $node)
    {
        $className = \get_class($xmlElement);

        switch ($className) {
            case XmlElement::class:
                return $this->formatXmlElementField($xmlElement, $node);
            break;

            case XmlElementGroup::class:
                return $this->formatXmlElementGroupField($xmlElement, $node);
                break;
        }
    }

    private function formatXmlElementField(XmlElement $xmlElement, $node): void
    {
        $arrOptions = $xmlElement->getOptions();
        $newElement = $this->dom->createElement($xmlElement->getName());

        // Add attributes
        $this->addAttributes($newElement, $xmlElement->getAttributes());

        if ($xmlElement->getContent()) {
            $strContent = $xmlElement->getContent();

            foreach ($this->arrFilter as $search => $replace) {
                $strContent = str_replace($search, $replace, $strContent);
            }

            if (isset($arrOptions['cdata']) && $arrOptions['cdata']) {
                $elementCdata = $this->dom->createCDATASection($strContent);
                $newElement->appendChild($elementCdata);
            } else {
                $newElement->textContent = $strContent;
            }
        }

        $node->appendChild($newElement);
    }

    private function formatXmlElementGroupField(XmlElementGroup $xmlElementGroup, $node): void
    {
        $newElement = $this->dom->createElement($xmlElementGroup->getName());

        // Add attributes
        $this->addAttributes($newElement, $xmlElementGroup->getAttributes());

        $items = $xmlElementGroup->getItemFields();

        foreach ($items as $xmlSubElement) {
            $className = \get_class($xmlSubElement);

            switch ($className) {
                // Element is a single field
                case XmlElement::class:
                    $this->formatXmlElementField($xmlSubElement, $newElement);
                break;

                // Element is a group item
                case XmlElementGroup::class:
                    $this->formatXmlElementGroupField($xmlSubElement, $newElement);
                break;
            }
        }

        $node->appendChild($newElement);
    }

    private function addAttributes(\DomElement $node, array $arrAttributes): void
    {
        foreach ($arrAttributes as $attrName => $attrValue) {
            $attribute = $this->dom->createAttribute($attrName);
            $attribute->value = $attrValue;
            $node->appendChild($attribute);
        }
    }
}
