<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\Adapter\Transaction\Request;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\FieldDefinitionObject;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

class EzPlatformAdapterTest extends EzPlatformTestCase
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

    public function testSendContentObject()
    {
        $contentObject = new ContentObject(array(
            'title' => 'Test',
        ));
        $contentObject->setContentType('_test_article');
        $contentObject->setLanguage('eng-GB');
        $contentObject->setRemoteId('test_1');

        $this->adapter->send(new Request(array(
            $contentObject,
        )));
    }

    public function testSendTreeObject()
    {
        $contentObject = new ContentObject(array(
            'title' => 'Test',
        ));
        $contentObject->setContentType('_test_article');
        $contentObject->setLanguage('eng-GB');
        $contentObject->setRemoteId('test_2');

        $treeObject = new TreeObject($contentObject);
        $treeObject->setProperty('location_id', 2);

        $this->adapter->send(new Request(array(
            $treeObject,
        )));
    }

    public function testSendFullContentTypeObject()
    {
        $ct = new ContentTypeObject('_test_article');
        $ct->mainLanguageCode = 'eng-GB';
        $ct->addName('Article', 'eng-GB');
        $ct->setNames(array('eng-GB' => 'Article'));
        $ct->addDescription('Article description');
        $ct->isContainer = true;
        $ct->defaultAlwaysAvailable = true;
        $ct->addContentTypeGroup('Content');
        $ct->defaultSortField = Location::SORT_FIELD_PUBLISHED;
        $ct->defaultSortOrder = Location::SORT_ORDER_DESC;
        $ct->nameSchema = '<name>';
        $ct->urlAliasSchema = '<name>';

        $f = new FieldDefinitionObject('name');
        $f->type = 'ezstring';
        $f->fieldGroup = 'content';
        $f->addName('Name', 'eng-GB');
        $f->setNames(array('eng-GB' => 'Name'));
        $f->addDescription('Name of the article');
        $f->isRequired = true;
        $f->isTranslatable = true;
        $f->isSearchable = true;
        $f->isInfoCollector = false;
        $f->defaultValue = '';

        $ct->addFieldDefinition($f);
        $this->adapter->send(new Request(array(
            $ct,
        )));
    }

    public function testSendMiniContentTypeObject()
    {
        $ct = new ContentTypeObject('_test_frontpage');
        $f = new FieldDefinitionObject('name');
        $ct->addFieldDefinition($f);
        $this->adapter->send(new Request(array(
            $ct,
        )));
    }
}
