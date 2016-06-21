<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\Adapter\Transaction\Request;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\tests\testcase\ContentTreeTestCase;

class ContentTreeTest extends ContentTreeTestCase
{
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
        ), 'tree_article_0', 'article');
        $folder = $this->getContentObject(array(
            'name' => 'Test folder',
        ), 'tree_folder_0', 'folder');
        $folder->addParentLocation(2);

        $tree = new TreeObject($folder);
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
        ), 'tree_article_0', 'article');
        $folder = $this->getContentObject(array(
            'name' => 'Updated folder',
        ), 'tree_folder_0', 'folder');

        $folder->addParentLocation(2);
        $tree = new TreeObject($folder);

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

    public function testTreeContentInTreeContent()
    {
        $topFolderContent = static::$repository->getContentService()->loadContentByRemoteId('tree_folder_0');
        $topFolder = new ContentObject([]);
        $topFolder->getMapper()->contentToObject($topFolderContent);

        $folder = $this->getContentObject(array(
            'name' => 'Updated folder',
        ), 'tree_folder_1', 'folder');

        $topFolder->addParentLocation(2);
        $tree1 = new TreeObject($topFolder);
        $folder->addParentLocation($topFolder->getProperty('content_info')->mainLocationId);
        $tree2 = new TreeObject($folder);
        $tree1->addNode($tree2);

        $this->adapter->send(new Request(array(
            $tree1,
        )));

        $childFolderContentInfo = static::$repository->getContentService()->loadContentInfoByRemoteId('tree_folder_1');
        $locations = static::$repository->getLocationService()->loadLocations($childFolderContentInfo);

        $childLocation = null;
        foreach ($locations as $location) {
            if ($location->contentId == $childFolderContentInfo->id) {
                $childLocation = $location;
                break;
            }
        }

        $this->assertEquals($topFolderContent->contentInfo->mainLocationId, $childLocation->parentLocationId);
    }
}
