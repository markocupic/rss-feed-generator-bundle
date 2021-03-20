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

use Symfony\Component\HttpFoundation\Response;

class Feed
{
    /**
     * @var \DOMDocument
     */
    public $xml;
    /**
     * @var array
     */
    private $arrFilter;

    /**
     * @var \DOMElement|\DOMNode
     */
    private $rss;

    /**
     * @var \DOMNode
     */
    private $channel;

    /**
     * @var string
     */
    private $encoding;

    public function __construct(array $arrFilter)
    {
        $this->arrFilter = $arrFilter;
    }

    /**
     * @return $this
     */
    public function create(string $encoding = 'utf-8'): self
    {
        $this->encoding = $encoding;

        $this->xml = new \DOMDocument('1.0', $this->encoding);

        // Format nicely
        $this->formatNicely(true);

        // Add comment
        $comment = $this->xml->createComment('Generated with markocupic/rss-feed-generator-bundle. See https://github.com/markocupic/rss-feed-generator-bundle');
        $this->xml->appendChild($comment);

        // Create element "rss"
        $rss = $this->xml->createElement('rss');
        $this->rss = $this->xml->appendChild($rss);
        $this->rss->setAttribute('version', '2.0');

        // Create element "channel"
        $channel = $this->xml->createElement('channel');
        $this->channel = $this->rss->appendChild($channel);

        return $this;
    }

    public function formatNicely(bool $bool): void
    {
        if ($bool) {
            $this->xml->preserveWhiteSpace = false;
            $this->xml->formatOutput = true;
        } else {
            $this->xml->preserveWhiteSpace = true;
            $this->xml->formatOutput = false;
        }
    }

    /**
     * mandatory channel field.
     */
    public function addTitle(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('title', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * mandatory channel field.
     */
    public function addLink(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('link', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * mandatory channel field.
     */
    public function addDescription(string $strValue, $blnCdata = true, array $arrFilter = [], array $arrAttributes = []): void
    {
        $strValue = preg_replace('/[\n\r]+/', ' ', $strValue);

        $this->appendChildToChannel('description', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addManagingEditor(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('managingEditor', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addWebMaster(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('webMaster', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addDocs(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('docs', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addCloud(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('cloud', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addLanguage(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('language', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addCopyright(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('copyright', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addPubDate(int $strValue, array $arrAttributes = []): void
    {
        $this->appendChildToChannel('pubDate', date('r', $strValue), false, [], $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addLastBuildDate(int $strValue, array $arrAttributes = []): void
    {
        $this->appendChildToChannel('lastBuildDate', date('r', $strValue), false, [], $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addTtl(int $strValue, array $arrAttributes = []): void
    {
        $this->appendChildToChannel('ttl', (string) $strValue, false, [], $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addCategory(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('category', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addAuthor(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('author', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * optional channel field.
     */
    public function addGenerator(string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel('generator', $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * Add additional elements to item.
     *
     * @param false $blnCdata
     * @param false $strValue
     */
    public function addAdditional(string $attrName, string $strValue, $blnCdata = false, array $arrFilter = [], array $arrAttributes = []): void
    {
        $this->appendChildToChannel($attrName, $strValue, $blnCdata, $arrFilter, $arrAttributes);
    }

    /**
     * Add an item element to the channel node.
     */
    public function addItem(FeedItem $feedItem): void
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
        $content = $this->xml->saveXML();

        if (\strlen($path)) {
            $this->saveInFilesystem($path, $content);
        }

        $response = new Response($content);
        $response->setCharset($this->encoding);
        $response->headers->set('Content-Type', 'application/rss+xml; charset='.$this->encoding.'; filename='.basename($path));

        return $response;
    }

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

    private function addAttributes(\DomElement $node, array $arrAttributes): void
    {
        foreach ($arrAttributes as $attrName => $attrValue) {
            $attribute = $this->xml->createAttribute($attrName);
            $attribute->value = $attrValue;
            $node->appendChild($attribute);
        }
    }

    /**
     * @return false|int
     */
    private function saveInFilesystem(string $path, string $content)
    {
        return file_put_contents($path, $content);
    }
}
