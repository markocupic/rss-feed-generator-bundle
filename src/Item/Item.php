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
    private string $name;
    private string $content;
    private array $arrOptions = [];
    private array $arrAttributes = [];

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
