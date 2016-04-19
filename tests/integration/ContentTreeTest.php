<?php

namespace Transfer\EzPlatform\Tests\integration;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

class ContentTreeTest extends EzPlatformTestCase
{
    /**
     * @var EzPlatformAdapter
     */
    protected $adapter;

    public function setUp()
    {
        $this->adapter = new EzPlatformAdapter(array(
            'repository' => static::$repository,
        ));
        $logger = $this->getMock(LoggerInterface::class);
        $this->adapter->setLogger($logger);
    }

    /**
     * Create a Folder which contains an Article.
     *
     * @throws \Exception
     */
    public function testCreateAndUpdateLocationsAndContent()
    {
        // Create
        $article = $this->getContentObject(array(
            'title' => 'Test article',
            'intro' => $this->getRichtext('<para>Article intro</para>'),
        ), 'article', 'tree_article_0');
        $folder = $this->getContentObject(array(
            'name' => 'Test folder',
        ), 'folder', 'tree_folder_0');

        $tree = $this->getTreeObject(2, $folder);
        $tree->addNode($article);

        $this->adapter->send(new Request(array(
            $tree,
        )));

        // Testing create result

        $contentFolder = static::$repository->getContentService()->loadContentByRemoteId('tree_folder_0');
        $this->assertInstanceOf(Content::class, $contentFolder);
        $this->assertEquals('Test folder', $contentFolder->getField('name')->value->text);

        $locationFolder = static::$repository->getLocationService()->loadLocations($contentFolder->contentInfo)[0];
        $this->assertInstanceOf(Location::class, $locationFolder);
        $this->assertEquals(2, $locationFolder->parentLocationId);

        $children = static::$repository->getLocationService()->loadLocationChildren($locationFolder);
        $this->assertEquals(1, $children->totalCount);
        $locationArticle = $children->locations[0];
        $this->assertInstanceOf(Location::class, $locationArticle);

        $contentArticle = static::$repository->getContentService()->loadContentByContentInfo($locationArticle->getContentInfo());
        $this->assertEquals('Test article', $contentArticle->getField('title')->value->text);
        $this->assertEquals($this->getRichtext('<para>Article intro</para>'), (string) $contentArticle->getField('intro')->value);

        $originalLocationFolderId = $locationFolder->id;
        $originalLocationArticleId = $locationArticle->id;

        unset($tree,
            $folder,
            $locationFolder,
            $contentFolder,
            $article,
            $locationArticle,
            $contentArticle,
            $children);

        // Update
        $article = $this->getContentObject(array(
            'title' => 'Updated article',
        ), 'article', 'tree_article_0');
        $folder = $this->getContentObject(array(
            'name' => 'Updated folder',
        ), 'folder', 'tree_folder_0');

        $tree = $this->getTreeObject(2, $folder);
        $tree->addNode($article);

        $this->adapter->send(new Request(array(
            $tree,
        )));

        // Test update result
        $contentFolder = static::$repository->getContentService()->loadContentByRemoteId('tree_folder_0');
        $this->assertInstanceOf(Content::class, $contentFolder);
        $this->assertEquals('Updated folder', $contentFolder->getField('name')->value->text);

        $locationFolder = static::$repository->getLocationService()->loadLocations($contentFolder->contentInfo)[0];
        $this->assertInstanceOf(Location::class, $locationFolder);
        $this->assertEquals(2, $locationFolder->parentLocationId);

        $children = static::$repository->getLocationService()->loadLocationChildren($locationFolder);
        $this->assertEquals(1, $children->totalCount);
        $locationArticle = $children->locations[0];
        $this->assertInstanceOf(Location::class, $locationArticle);

        $contentArticle = static::$repository->getContentService()->loadContentByContentInfo($locationArticle->getContentInfo());
        $this->assertEquals('Updated article', $contentArticle->getField('title')->value->text);

        $this->assertEquals($originalLocationFolderId, $locationFolder->id);
        $this->assertEquals($originalLocationArticleId, $locationArticle->id);
    }

}
