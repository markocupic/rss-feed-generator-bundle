<img src="./src/Resources/public/logo.png" width="300">

# RSS Feed Generator Bundle
Use this bundle to generate rss feeds inside your Symfony application.


&#10084; Many thanks to @eko (Vincent Composieux) for giving me the inspiration to program this bundle. https://github.com/eko/FeedBundle.

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

**Option B:** In a **Contao** &#10084; environment register the rss feed generator bundle in the **Contao Manager Plugin** class of your bundle.

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

### Create the feed 
```php
// Use the feed factory to generate the feed object
$rss = $this->feedFactory->createFeed('utf-8');
```
### Add feed Channel elements
Use the Item class inside the feed factory method FeedFactory::addChannelField().

The Item::__constructor($elementName, $strValue, $arrOptions, $arrAttributes) takes four arguments:

1. (string) element name
2. (string) content
3. optional: (array) options (at the moment cdata, and filters)
4. optional: (array) with attributes

```php
$rss->addChannelField(
    new Item('title', 'Demo feed')
);

$rss->addChannelField(
    new Item('link', 'https://foobar.ch')
);
```
Make cdata elements and insert attributes:
```php
// Add CDATA element and an attribute
$rss->addChannelField(
    new Item('description', 'Check our news feed and have fun!', ['cdata' => true], ['attrName' => 'Here comes the attribute value'])
);
```

### Filter od replace content
```php
// filter or replace values
$arrFilter = ['Ferrari' => 'Italia', 'car' => 'country'] ;
$rss->addChannelField(
    new Item('description', 'Ferrari is my favourite car!', ['filter' => $arrFilter])
);
// Will result in:
// <description>Italia is my favourite country!</description>
```


### Add channel items
Use FeedFactory::addChannelItemField(), ItemGroup() and Item() to generate channel items.

The ItemGroup::__constructor($elementName, $arrItemObjects, $arrAttributes) takes three arguments:
1. (string) element name
2. (array) with Item objects
3. optional: (array) with attributes
```php


// Retrieve data from database and add items
$results = $this->getEvents($section);

if (null !== $results) {
    while (false !== ($arrEvent = $results->fetch())) {
        // Use a new instance of ItemGroup to add a collection of items all of the same level.
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
// Append nested items with ItemGroup.
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
```
Result:
```xml
<item>
    <title>Title</title>
    <link>https://foo.bar</link>
    <nestedItem foo="bar">
        <subitem>Some content</subitem>
        <subitem>Some content</subitem>
    </nestedItem>
</item>
```

### Render and send content to the browser.
```php
return $rss->render();
```

### Render and save content to the filesystem.
```php
return $rss->render('web/share/myfeed.xml);
```


## Generate RSS 2.0 feed inside a controller in a Symfony bundle

```php
<?php

declare(strict_types=1);

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
     * @Route("/_rssfeed", name="rss_feed")
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
        
        // Add some channel items
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

## Filter & search and replace strings
The extension will filter by default some characters. Linebreaks will be replaced with a whitespace, etc.
Please have a look at the [Plugin Configuration](https://github.com/markocupic/rss-feed-generator-bundle/blob/main/src/DependencyInjection/Configuration.php#L30).

Overriding these defaults is pretty easy and can be done in config/parameters.yml. 
Please use regular expressions for the search patterns.

```xml
# config/parameters.yml
markocupic_rss_feed_generator:
  filter:
    # '/[\n\r]+/': ' ' Disabled
    '/&#40;/': '('
    '/&#41;/': ')'
    '/\[-\]/': ''
    '/\&shy;/': ''
    '/\[nbsp\]/': ' '
    '/&nbsp;/': ' '
    '/foo/': 'bar' #Added

```

