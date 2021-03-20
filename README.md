<img src="./src/Resources/public/logo.png" width="300">

# Welcome to RSS Feed GeneratorBundle
Use this bundle to generate rss feeds with ease.

## Installation
`composer require markocupic/rss-feed-generator-bundle`

Add this to your config/bundles.php

```php
<?php

return [
    // ...
    Markocupic\RssFeedGeneratorBundle\MarkocupicRssFeedGeneratorBundle::class => ['all' => true],
];
```

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

### Create feed
```php
// Use factory to generate the feed object
$rss = $this->feedFactory->createFeed('utf-8');

$rss->addTitle('Demo feed');
$rss->addDescription('Latest demo events');
$rss->addLink('https://foobar.ch');
// etc.
// Add additional elements (cdata = true)
$rss->addAdditional('superField', 'some value', true);
```

### Filter oder replace content
```php
// filter or replace values
$arrFilter = ['Foo' => '', 'bar' => 'foo'] ;
$rss = $this->feedFactory->createFeed('utf-8');
```

### Add attributes
```php
// add attributes to the element
$arrAttributes = ['src' => 'https://demo.ch', 'foo' => 'bar'] ;
$rss = $this->feedFactory->createFeed('utf-8');
$rss->addAdditional('superField', 'some value', true, [], $arrAttributes); // <superField src="https://demo.ch" foo="bar">some value</superField>
```

### Add items
```
$rss = $this->feedFactory->createFeed('utf-8');

$rss->addTitle('Demo feed');
$rss->addDescription('Latest demo events');
// etc.

// Use Factory to create feed item
$item = $this->feedFactory->createFeedItem();

// Add elements to the item
$item->addTitle('Title');
$item->addDescription('Here comes the description');
$item->addLink('https://foobar.ch');
$item->addAuthor('gaston_rebuffat@montblanc.fr');
// etc. 

// Add item node to the document
$rss->addItem($item);
```

### Render and send content to browser.
```
return $rss->render();
```

### Or render and save content to the filesystem.
```
return $rss->render('web/share/myfeed.xml);
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
    public function generateFeed(): Response
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

## Result
```xml
<?xml version="1.0" encoding="utf-8"?>
<!--Generated with markocupic/rss-feed-generator-bundle. See https://github.com/markocupic/rss-feed-generator-bundle-->
<rss version="2.0">
  <channel>
    <title>Demo feed</title>
    <description><![CDATA[Latest demo events]]></description>
    <link>https://myfancy-website.ch</link>
    <language>en</language>
    <copyright>Copyright 2021, Gaston Rébuffat</copyright>
    <pubDate>Sat, 20 Mar 2021 19:07:06 +0100</pubDate>
    <lastBuildDate>Sat, 20 Mar 2021 20:07:06 +0100</lastBuildDate>
    <ttl>60</ttl>
    <category>Fancy Events</category>
    <item>
      <title>Pizzo d'Orsirora</title>
      <description><![CDATA[Ski- und Snowboardtour auf der unbekannteren, weniger begangenen "Dark Side" von Realp mit kleinem Gipfel.]]></description>
      <link>https://myfancy-website.ch/feed/4567</link>
      <pubDate>Mon, 15 Mar 2021 20:07:34 +0100</pubDate>
      <author>gaston_rebuffat@montblanc.fr</author>
      <guid>https://myfancy-website.ch/feed/4567</guid>
      <tourdb:startdate>2021-03-21</tourdb:startdate>
      <tourdb:endDate>2021-03-21</tourdb:endDate>
    </item>
  </channel>
</rss>

```