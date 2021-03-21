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

namespace Markocupic\RssFeedGeneratorBundle;

use Markocupic\RssFeedGeneratorBundle\DependencyInjection\MarkocupicRssFeedGeneratorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MarkocupicRssFeedGeneratorBundle.
 */
class MarkocupicRssFeedGeneratorBundle extends Bundle
{
    public function getContainerExtension(): MarkocupicRssFeedGeneratorExtension
    {
        return new MarkocupicRssFeedGeneratorExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
