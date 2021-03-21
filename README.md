<img src="./src/Resources/public/logo.png" width="300">

# RSS Feed Generator Bundle
Use this bundle to generate rss feeds inside your Symfony application.


&#10084; Big thanks to Fabien Composieux for giving me the inspiration to program this bundle. https://github.com/eko/FeedBundle.

## Installation
`composer require markocupic/rss-feed-generator-bundle`

**Option A:** Add this to your config/bundles.php.

```php
<?php

return [
    // ...
    Markocupic\RssFeedGeneratorBundle\MarkocupicRssFeedGeneratorBundle::class => ['all' => true],
];
```
**Option B:** In a **Contao** &#10084; environment register the rss feed generator bundle in the **Contao Manager Plugin* class of your bundle.
```php
<?php

declare(strict_types=1);

namespace Contao\CoreBundle\ContaoManager;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Markocupic\RssFeedGeneratorBundle\MarkocupicRssFeedGeneratorBundle;
use Acme\MyBundle\AcmeMyBundleBundle;

/**
 * Class Plugin
 * Plugin for the Contao Manager.
 */
class Plugin implements BundlePluginInterface, RoutingPluginInterface, ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            // Register RSS feed generator bundle
            BundleConfig::create(MarkocupicRssFeedGeneratorBundle::class),
            // register other bundles
            BundleConfig::create(AcmeMyBundle::class)
            ->setLoadAfter(MarkocupicRssFeedGeneratorBundle::class)
            ->setLoadAfter(ContaoCoreBundle::class)
        ];
    }

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

$rss->addChannelField(
    new Item('title', 'Demo feed')
);

// Add CDATA element and an attribute
$rss->addChannelField(
    new Item('description', 'Check our news feed and have fun!', ['cdata' => true], ['attrName' => 'Here comes the attribute value'])
);

$rss->addChannelField(
    new Item('link', 'https://foobar.ch')
);

```

### Filter od replace content
```php
// filter or replace values
$arrFilter = ['Ferrari' => 'Italia', 'car' => 'country'] ;
$rss->addChannelField(
    new Item('description', 'Ferrari is my favourite car!', ['filter' => $arrFilter])
);
// <description>Italia is my favourite country!</description>
```


### Add items
```php
// Use factory to generate the feed object
$rss = $this->feedFactory->createFeed('utf-8');

$rss->addChannelField(
    new Item('title', 'Demo feed')
);
// ...

// Retrieve data from database and add Items
$results = $this->getEvents($section);

if (null !== $results) {
    while (false !== ($arrEvent = $results->fetch())) {
        // Use ItemGroup to add a collection of items all of the same level.
        $rss->addChannelItemField(
            new ItemGroup('item', [
                new Item('title', $arrEvent['title']),
                new Item('link', $arrEvent['link']),
                new Item('description', $arrEvent['description'], ['cdata' => true]),
                new Item('pubDate', date('r',(int) $arrEvent['tstamp'])),
                new Item('author', $arrEvent['author']),
                new Item('guid', $arrEvent['uuid']),
                new Item('tourdb:startdate', date('Y-m-d', (int) $arrEvent['startDate'])),
                new Item('tourdb:enddate', date('Y-m-d', (int) $arrEvent['endDate'])),
            ])
        );
    }
}
```

### Nested items
```php

// Apend nested items with ItemGroup.
$rss->addChannelItemField(
    new ItemGroup('item', [
        new Item('title', 'Title'),
        new Item('link', 'https://foo.bar'),
        new ItemGroup('nestedItems', [
            new Item('subitem', 'Some content'),
            new Item('subitem', 'Some content'),
        ], ['foo'=> 'bar']),
    ])
);

...
<item>
    <title>Title</title>
    <link>https://foo.bar</link>
    <nestedItem>
        <subitem>Some content</subitem>
        <subitem>Some content</subitem>
    </nestedItem>
</item>

```

### Render and send content to the browser.
```
return $rss->render();
```

### Render and save content to the filesystem.
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

namespace Acme\DemoBundle\Controller\Feed;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Markocupic\RssFeedGeneratorBundle\Feed\FeedFactory;
use Markocupic\RssFeedGeneratorBundle\Item\Item;
use Markocupic\RssFeedGeneratorBundle\Feed\ItemGroup;
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
     * @Route("/_rssfeeds", name="rss_feed")
     */
    public function printLatestEvents(): Response
    {

        $rss = $this->feedFactory->createFeed('utf-8');

        $rss->addChannelField(
            new Item('title', 'Acme news')
        );

        $rss->addChannelField(
            new Item('description', 'Enjoj our news.')
        );

        $rss->addChannelField(
            new Item('link', 'https://acme.com')
        );

        $rss->addChannelField(
            new Item('language', 'de')
        );

        $rss->addChannelField(
            new Item('pubDate', date('r', (time() - 3600)))
        );

        // Retrieve data from db
        $results = $this->getEvents($section);
        
        // Add some channe items
        if (null !== $results) {
            while (false !== ($arrEvent = $results->fetch())) {
                $eventsModel = $calendarEventsModelAdapter->findByPk($arrEvent['id']);

                $rss->addChannelItemField(
                    new ItemGroup('item', [
                        new Item('title', $arrEvent['title']),
                        new Item('link', $arrEvent['link']),
                        new Item('description', $arrEvent['description'], ['cdata' => true]),
                        new Item('pubDate', date('r', (int) $eventsModel->tstamp)),
                    ])
                );
            }
        }

        return $rss->render($this->projectDir.'/web/share/rss.xml');
    }
}

```
