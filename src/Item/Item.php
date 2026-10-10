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

namespace Markocupic\RssFeedGeneratorBundle\Item;

class Item implements ItemInterface
{
    /**
     * @param string $name          The element name
     * @param string $content       The element content
     * @param array  $arrOptions    Options: "cdata" (bool) and "filter" (array, search => replace regular expressions)
     * @param array  $arrAttributes Attributes of the element
     */
    public function __construct(
        private readonly string $name,
        private readonly string $content,
        private readonly array $arrOptions = [],
        private readonly array $arrAttributes = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContent(): string|null
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
