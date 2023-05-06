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

namespace Markocupic\RssFeedGeneratorBundle\Feed;

use Markocupic\RssFeedGeneratorBundle\Formatter\Formatter;
use Markocupic\RssFeedGeneratorBundle\Item\ItemInterface;
use Symfony\Component\HttpFoundation\Response;

class Feed
{
    public const ENCODING_UTF8 = 'utf-8';

    public \DOMDocument $xml;
    private Formatter $formatter;
    private string $version;
    private array $arrRootAttributes = [];
    private array $arrChannelAttributes = [];
    private array $channelFields = [];
    private array $channelItemFields = [];
    private string $encoding;
    private bool $formatNicely = true;

    public function __construct(Formatter $formatter, string $encoding)
    {
        $this->formatter = $formatter;
        $this->encoding = $encoding;
        $this->version = '2.0';
    }

    public function getRootAttributes(): array
    {
        return $this->arrRootAttributes;
    }

    public function getChannelAttributes(): array
    {
        return $this->arrChannelAttributes;
    }

    public function setRootAttributes(array $arrAttributes): void
    {
        $this->arrRootAttributes = $arrAttributes;
    }

    public function setChannelAttributes(array $arrAttributes): void
    {
        $this->arrChannelAttributes = $arrAttributes;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getFormatNicely(): bool
    {
        return $this->formatNicely;
    }

    public function doFormatNicely(bool $bool): self
    {
        $this->formatNicely = $bool;

        return $this;
    }

    public function getChannelFields(): array
    {
        return $this->channelFields;
    }

    public function getChannelItemFields(): array
    {
        return $this->channelItemFields;
    }

    public function addChannelField(ItemInterface $objItem): self
    {
        $this->channelFields[] = $objItem;

        return $this;
    }

    public function addChannelItemField(ItemInterface $objItem): self
    {
        $this->channelItemFields[] = $objItem;

        return $this;
    }

    /**
     * If param $path is set, the extension will try to save
     * the output in the filesystem.
     */
    public function render(string $path = ''): Response
    {
        $strBuffer = $this->formatter->render($this, $path);

        if (\strlen($path)) {
            $this->writeFileToFilesystem(
                $path,
                $strBuffer
            );
        }

        $response = new Response($strBuffer);
        $response->setCharset($this->getEncoding());

        $strFilename = \strlen((string) $path) ? '; filename='.basename($path) : '';
        $response->headers->set('Content-Type', sprintf('application/rss+xml; charset=%s%s', $this->getEncoding(), $strFilename));

        return $response;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function setEncoding(string $strEncoding): self
    {
        $this->encoding = $strEncoding;

        return $this;
    }

    /**
     * @return false|int
     */
    private function writeFileToFilesystem(string $path, string $content)
    {
        return file_put_contents($path, $content);
    }
}
