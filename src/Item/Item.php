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

namespace Markocupic\RssFeedGeneratorBundle\Item;

class Item implements ItemInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $arrOptions = [];

    /**
     * @var array
     */
    private $arrAttributes = [];

    /**
     * Item constructor.
     * @param string $name
     * @param string $content
     * @param array $arrOptions
     * @param array $arrAttributes
     */
    public function __construct(string $name, string $content, array $arrOptions = [], array $arrAttributes = [])
    {
        $this->name = $name;
        $this->content = $content;
        $this->arrOptions = $arrOptions;
        $this->arrAttributes = $arrAttributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getOptions(): array
    {
        return $this->arrOptions;
    }

    public function getAttributes(): array
    {
        return $this->arrAttributes;
    }
}
