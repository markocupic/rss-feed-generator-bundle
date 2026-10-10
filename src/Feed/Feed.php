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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

class Feed
{
    public const ENCODING_UTF8 = 'utf-8';

    private string $version = '2.0';

    private array $arrRootAttributes = [];

    private array $arrChannelAttributes = [];

    /**
     * @var array<ItemInterface>
     */
    private array $channelFields = [];

    /**
     * @var array<ItemInterface>
     */
    private array $channelItemFields = [];

    private bool $formatNicely = true;

    public function __construct(
        private readonly Formatter $formatter,
        private string $encoding = self::ENCODING_UTF8,
    ) {
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

    /**
     * @return array<ItemInterface>
     */
    public function getChannelFields(): array
    {
        return $this->channelFields;
    }

    /**
     * @return array<ItemInterface>
     */
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
     * Renders the feed. If $path is set, the output will also be saved to the
     * filesystem (missing directories are created).
     */
    public function render(string $path = ''): Response
    {
        $strBuffer = $this->formatter->render($this);

        if ('' !== $path) {
            (new Filesystem())->dumpFile($path, $strBuffer);
        }

        $response = new Response($strBuffer);
        $response->setCharset($this->getEncoding());

        $strFilename = '' !== $path ? '; filename='.basename($path) : '';
        $response->headers->set('Content-Type', \sprintf('application/rss+xml; charset=%s%s', $this->getEncoding(), $strFilename));

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
}
