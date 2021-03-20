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

class FeedItem
{

    private $arrData = [];

    public function create(): self
    {
        return $this;
    }

    public function addTitle(string $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add('title', $strValue, $blnCdata, $arrAttributes);
    }

    public function addLink(string $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add('link', $strValue, $blnCdata, $arrAttributes);
    }

    public function addDescription(string $strValue, $blnCdata = true, array $arrAttributes = []): void
    {
        $strValue = preg_replace('/[\n\r]+/', ' ', $strValue);
        $this->add('description', $strValue, $blnCdata, $arrAttributes);
    }

    public function addAuthor(string $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add('author', $strValue, $blnCdata, $arrAttributes);
    }

    public function addCategory(string $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add('category', $strValue, $blnCdata, $arrAttributes);
    }

    public function addComments(string $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add('comments', $strValue, $blnCdata, $arrAttributes);
    }

    public function addEnclosure(string $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add('enclosure', $strValue, $blnCdata, $arrAttributes);
    }

    public function addGuid(string $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add('guid', $strValue, $blnCdata, $arrAttributes);
    }

    public function addPubDate(int $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add('pubDate', date('r', $strValue), $blnCdata, $arrAttributes);
    }

    public function addSource(string $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add('guid', $strValue, $blnCdata, $arrAttributes);
    }

    public function addAdditional(string $strAttr, string $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $this->add($strAttr, $strValue, $blnCdata, $arrAttributes);
    }

    public function getData(): array
    {
        return $this->arrData;
    }

    public function hasData(): bool
    {
        return !empty($this->arrData);
    }

    private function add(string $strAttr, $strValue, $blnCdata = false, array $arrAttributes = []): void
    {
        $arrItem = [
            'value' => $strValue,
            'cdata' => $blnCdata,
            // Add attributes (key => value pair)
            'attributes' => empty($arrAttributes) ? null : $arrAttributes,
        ];

        $this->arrData[$strAttr] = $arrItem;
    }
}
