<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

/**
 * Content manager tests.
 */
class ContentManagerTest extends EzPlatformTestCase
{
    public function testCreate()
    {
        $manager = new ContentManager(static::$repository);


            $contentObject = new ContentObject(array(
                'name' => 'Test title',
                'title' => 'Test title',
                'description' => 'Test description',
            ));
            $contentObject->setContentType('_test_article');
            $contentObject->setLanguage('eng-GB');
            $contentObject->setRemoteId('_test_1');

            $manager->create($contentObject);

            $createdContentObject = $manager->findByRemoteId('_test_1');

            $this->assertEquals('Test title', (string) $createdContentObject->data['title']['eng-GB']);
            $this->assertEquals('Test description', (string) $createdContentObject->data['description']['eng-GB']);

    }
}
