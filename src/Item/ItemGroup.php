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

class ItemGroup implements ItemInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $itemFields = [];

    /**
     * @var array
     */
    private $arrAttributes = [];

    /**
     * ItemGroup constructor.
     *
     * @param array|ItemInterface $itemFields
     */
    public function __construct(string $name, $itemFields, array $arrAttributes = [])
    {
        $this->name = $name;

        if (!\is_array($itemFields) && !$itemFields instanceof ItemInterface) {
            throw new \RuntimeException('ItemGroup second argument shouls be an array or a singele ItemField instance.');
        }

        if (!\is_array($itemFields)) {
            $itemFields = [$itemFields];
        }

        $this->itemFields = $itemFields;
        $this->arrAttributes = $arrAttributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributes(): array
    {
        return $this->arrAttributes;
    }

    public function getChannelItemFields(): array
    {
        return $this->itemFields;
    }
}
