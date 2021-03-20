![Alt text](src/Resources/public/logo.png?raw=true "logo")


# Welcome to RSS Feed GeneratorBundle
Use this bundle to generate rss feeds with ease.

## Installation
`composer require markocupic/rss-feed-generator-bundle`


Use dependency injection to require the feed factory in your controller.

```
# src/Resources/config/services.yml
services:

  Markocupic\DemoBundle\Controller\Feed\FeedController:
    arguments:
      - '@Markocupic\RssFeedGeneratorBundle\Feed\FeedFactory'
      - '@database_connection'
      - '%kernel.project_dir%'
    public: true
```

## Generate RSS 2.0 feed inside a controller in a Symfony bundle

```php
<?php

declare(strict_types=1);

/*
 * This file is part of feed demo bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rss-feed-generator-bundle
 */

namespace Markocupic\DemoBundle\Controller\Feed;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Markocupic\RssFeedGeneratorBundle\Feed\FeedFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedController extends AbstractController
{
    /**
     * @var FeedFactory
     */
    private $feedFactory;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * FeedController constructor.
     */
    public function __construct(FeedFactory $feedFactory, Connection $connection, string $projectDir)
    {
        $this->feedFactory = $feedFactory;
        $this->connection = $connection;
        $this->projectDir = $projectDir;
    }

    /**
     * Generate RSS Feed.
     *
     * @Route("/_feed", name="demo_feed")
     */
    public function generateFeed(int $section = 4250): Response
    {
        // Use factory to generate the feed object
        $rss = $this->feedFactory->createFeed('utf-8');

        $rss->addTitle('Demo feed');
        $rss->addDescription('Latest demo events');
        $rss->addLink('https://foobar.ch');
        $rss->addLanguage('en');
        $rss->addCopyright('Copyright '.date('Y').', Gaston Rébuffat');
        $rss->addPubDate(time() - 3600);
        $rss->addLastBuildDate(time());
        $rss->addTtl(60);
        $rss->addCategory('Fancy Events');

        /**
         * Retrieve data from db.
         *
         * @var QueryBuilder $qb
         */
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from('tl_calendar_events', 't')
            ->where('t.published = :published')
            ->andWhere('t.startDate > :startDate')
            ->setParameter('published', '1')
            ->setParameter('startDate', time())
            ->orderBy('t.startDate', 'ASC')
            ->setMaxResults(50)
        ;

        $results = $qb->execute();

        // Now add some items
        if (null !== $results) {
            while (false !== ($arrEvent = $results->fetch())) {
                // Use Factory to create feed item
                $item = $this->feedFactory->createFeedItem();

                // Now add elements to the item
                $item->addTitle($arrEvent['title']);
                $item->addLink($arrEvent['link']);
                $item->addDescription($arrEvent['teaser'], true);
                $item->addPubDate((int) $arrEvent['tstamp']);
                $item->addAuthor($arrEvent['authorEmail']);
                $item->addGuid($arrEvent['guuid']);
                $item->addAdditional('tourdb:startdate', date('Y-m-d', (int) $arrEvent['startDate']));
                $item->addAdditional('tourdb:endDate', date('Y-m-d', (int) $arrEvent['endDate']));

                // Add item node to the document
                $rss->addItem($item);
            }
        }

        /*
         * call $rss->render() with path parameter to store content inthe filesystem
         *
         * $filename = $this->projectDir.'/web/share/rss_fancy_feed_.xml';
         * $rss->render($filename);
         */
        return $rss->render();
    }
}

```
