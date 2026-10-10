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

class ItemGroup implements ItemInterface
{
    /**
     * @var array<ItemInterface>
     */
    private readonly array $itemFields;

    /**
     * @param array<ItemInterface>|ItemInterface $itemFields
     */
    public function __construct(
        private readonly string $name,
        ItemInterface|array $itemFields,
        private readonly array $arrAttributes = [],
    ) {
        $itemFields = \is_array($itemFields) ? $itemFields : [$itemFields];

        foreach ($itemFields as $itemField) {
            if (!$itemField instanceof ItemInterface) {
                throw new \InvalidArgumentException(\sprintf('The second argument of %s() must be an instance of %s or an array of %s instances.', __METHOD__, ItemInterface::class, ItemInterface::class));
            }
        }

        $this->itemFields = array_values($itemFields);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributes(): array
    {
        return $this->arrAttributes;
    }

    /**
     * @return array<ItemInterface>
     */
    public function getChannelItemFields(): array
    {
        return $this->itemFields;
    }
}
