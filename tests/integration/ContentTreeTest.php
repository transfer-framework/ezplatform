<?php

namespace Transfer\EzPlatform\tests\integration;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\Adapter\Transaction\Request;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentObject;
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

        unset($tree);
        unset($folder);
        unset($locationFolder);
        unset($contentFolder);
        unset($article);
        unset($locationArticle);
        unset($contentArticle);
        unset($children);

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

    /**
     * Creates a TreeObject skeleton.
     *
     * @param int   $locationId
     * @param array $data
     *
     * @return TreeObject
     */
    private function getTreeObject($locationId, $data)
    {
        $tree = new TreeObject($data);
        $tree->setProperty('location_id', $locationId);

        return $tree;
    }

    /**
     * Creates a ContentObject skeleton.
     *
     * @param array  $data
     * @param string $contenttype
     * @param string $remoteId
     *
     * @return ContentObject
     */
    private function getContentObject(array $data, $contenttype, $remoteId)
    {
        $content = new ContentObject($data);
        $content->setContentType($contenttype);
        $content->setRemoteId($remoteId);
        $content->setLanguage('eng-GB');

        return $content;
    }

    /**
     * @param $content
     *
     * @return string
     */
    private function getRichtext($content)
    {
        return sprintf(
'<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0">%s</section>
',
        $content);
    }
}
