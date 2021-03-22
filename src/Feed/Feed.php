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
use Markocupic\RssFeedGeneratorBundle\Item\ItemInterface;
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
     * @var array
     */
    private $channelFields = [];

    /**
     * @var array
     */
    private $channelItemFields = [];

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

    public function getChannelItemFields(): array
    {
        return $this->channelItemFields;
    }

    public function setEncoding(string $strEncoding): self
    {
        $this->encoding = $strEncoding;

        return $this;
    }

    public function setFormatNicely(bool $bool): self
    {
        $this->formatNicely = $bool;

        return $this;
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
     * if param $path is set, the extension will try to save
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

    /**
     * @return false|int
     */
    private function writeFileToFilesystem(string $path, string $content)
    {
        return file_put_contents($path, $content);
    }
}
