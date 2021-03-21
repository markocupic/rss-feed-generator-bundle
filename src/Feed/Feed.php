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

use Markocupic\RssFeedGeneratorBundle\Formatter\Formatter;
use Markocupic\RssFeedGeneratorBundle\XmlElement\XmlElementInterface;
use Symfony\Component\HttpFoundation\Response;

class Feed
{
    /**
     * @var \DOMDocument
     */
    public $xml;

    /**
     * @var string
     */
    private $version;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var \DOMElement|\DOMNode
     */
    private $rss;

    /**
     * @var \DOMNode
     */
    private $channel;

    /**
     * @var array
     */
    private $channelFields = [];

    /**
     * @var array
     */
    private $channelItems = [];

    /**
     * @var string
     */
    private $encoding;

    /**
     * @var bool
     */
    private $formatNicely = true;

    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @return $this
     */
    public function create(): self
    {
        $this->encoding = 'utf-8';
        $this->version = '2.0';

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getFormatNicely(): bool
    {
        return $this->formatNicely;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function getChannelFields(): array
    {
        return $this->channelFields;
    }

    public function getItemFields(): array
    {
        return $this->channelItems;
    }

    public function setEncoding(string $strEncoding): self
    {
        return $this->encoding = $strEncoding;

        return $this;
    }

    public function setFormatNicely(bool $blnFormatNicely): self
    {
        return $this->formatNicely = $blnFormatNicely;

        return $this;
    }

    public function addChannelField(XmlElementInterface $xmlElement): self
    {
        $this->channelFields[] = $xmlElement;

        return $this;
    }

    public function addItemField(XmlElementInterface $xmlElementGroup): self
    {
        $this->channelItems[] = $xmlElementGroup;

        return $this;
    }

    /**
     * @todo remove
     * Add an item element to the channel node.
     */
    public function add(FeedItem $feedItem): void
    {
        if (!$feedItem->hasData()) {
            return;
        }

        $itemNode = $this->channel->appendChild($this->xml->createElement('item'));

        foreach ($feedItem->getData() as $key => $arrItem) {
            $strValue = $arrItem['value'];

            if (!isset($arrItem['filter']) || empty($arrItem['filter'])) {
                $arrFilter = $this->arrFilter;
            } else {
                $arrFilter = $arrItem['filter'];
            }

            // Replace '[-]', '&shy;', '[nbsp]', '&nbsp;' with empty or whitespace string
            foreach ($arrFilter as $search => $replace) {
                $strValue = str_replace($search, $replace, $strValue);
            }

            $element = $this->xml->createElement($key);

            if ($arrItem['cdata']) {
                $elementCdata = $this->xml->createCDATASection($strValue);
                $element->appendChild($elementCdata);
            } else {
                $element->textContent = $strValue;
            }

            // Remove node if it already exists
            $nodes = $itemNode->getElementsByTagName($key);

            foreach ($nodes as $node) {
                $itemNode->removeChild($node);
            }

            // Add Attributes
            if (!empty($arrItem['attributes']) && \is_array($arrItem['attributes'])) {
                $this->addAttributes($element, $arrItem['attributes']);
            }

            $itemNode->appendChild($element);
        }
    }

    /**
     * if param $path is set, the extension will try to save
     * the output in the filesystem.
     */
    public function render(string $path = ''): Response
    {


        $strBuffer = $this->formatter->render($this, $path);

        if (\strlen($path)) {
            $this->saveInFilesystem($path,

                $strBuffer);
        }


        $response = new Response($strBuffer);
        $response->setCharset($this->getEncoding());
        $strFilename = \strlen((string) $path) ? '; filename='.basename($path) : '';
        $response->headers->set('Content-Type', sprintf('application/rss+xml; charset=%s%s', $this->getEncoding(), $strFilename));

        return $response;

    }

    /**
     * @tod remove
     *
     * @param false $blnCdata
     */
    private function appendChildToChannel(string $strNodeName, string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        if (empty($arrFilter)) {
            $arrFilter = $this->arrFilter;
        }

        // Replace '[-]', '&shy;', '[nbsp]', '&nbsp;' with empty or whitespace string
        foreach ($arrFilter as $search => $replace) {
            $strValue = str_replace($search, $replace, $strValue);
        }

        $element = $this->xml->createElement($strNodeName);

        if ($blnCdata) {
            $elementCdata = $this->xml->createCDATASection($strValue);
            $element->appendChild($elementCdata);
        } else {
            $element->textContent = $strValue;
        }

        // Remove node if it already exists
        $nodes = $this->channel->getElementsByTagName($strNodeName);

        foreach ($nodes as $node) {
            $this->channel->removeChild($node);
        }

        // Add attributes
        if (!empty($arrAttributes) && \is_array($arrAttributes)) {
            $this->addAttributes($element, $arrAttributes);
        }

        $this->channel->appendChild($element);
    }

    /**
     * @return false|int
     */
    private function saveInFilesystem(string $path, string $content)
    {
        return file_put_contents($path, $content);
    }
}
