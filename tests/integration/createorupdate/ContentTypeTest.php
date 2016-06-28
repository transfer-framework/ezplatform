<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\FieldDefinitionObject;
use Transfer\EzPlatform\tests\testcase\ContentTypeTestCase;

class ContentTypeTest extends ContentTypeTestCase
{
    public function testCreateAndUpdateContentType()
    {
        $identifier = 'product';

        $contentObject = $this->getContentTypeMini($identifier);

        $this->adapter->send(new Request(array(
            $contentObject,
        )));
        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);

        $this->assertInstanceOf(ContentType::class, $real);
        $this->assertEquals('Product', $real->getName('eng-GB'));

        $raw = $this->getContentTypeFull($identifier);
        $raw->data['names']['eng-GB'] = 'Updated name';

        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);

        $this->assertInstanceOf(ContentType::class, $real);
        $this->assertEquals('Updated name', $real->getName('eng-GB'));
    }

    public function testDetatchContentGroup()
    {
        $identifier = 'product';

        $raw = $this->getContentTypeFull($identifier);
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);
        $this->assertCount(2, $real->getContentTypeGroups());

        $raw = $this->getContentTypeFull($identifier);
        $raw->data['contenttype_groups'] = array('Content');
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);
        $this->assertCount(1, $real->getContentTypeGroups());
    }

    /**
     * Tests content struct callback.
     */
    public function testStructCallback()
    {
        $remoteId = 'test_integration_contenttype_3';
        $identifier = 'struct_example';

        $contentTypeObject = $this->getContentTypeMini($identifier);

        $contentTypeObject->setStructCallback(function (ContentTypeCreateStruct $struct) use ($remoteId) {
            $struct->isContainer = false;
            $struct->defaultSortField = Location::SORT_FIELD_SECTION;
            $struct->remoteId = $remoteId;
        });

        $this->adapter->send(new Request(array(
            $contentTypeObject,
        )));

        $contentType = static::$repository->getContentTypeService()->loadContentTypeByRemoteId($remoteId);

        $this->assertEquals($remoteId, $contentType->remoteId);
        $this->assertEquals(Location::SORT_FIELD_SECTION, $contentType->defaultSortField);
        $this->assertFalse($contentType->isContainer);
    }

    /**
     * Tests field definitions struct callback.
     */
    public function testFieldDefinitionStructCallback()
    {
        $remoteId = 'test_integration_fielddefinition_1';
        $identifier = 'struct_fielddefinition_example';
        $fieldDefinitionIdentifier = 'my_field';

        $contentTypeObject = $this->getContentTypeMini($identifier);
        $contentTypeObject->setStructCallback(function (ContentTypeCreateStruct $struct) use ($remoteId) {
            $struct->remoteId = $remoteId;
        });

        $fieldDefinition = new FieldDefinitionObject($fieldDefinitionIdentifier, $contentTypeObject, array(
            'type' => 'ezstring',
        ));

        $fieldDefinition->setStructCallback(function (FieldDefinitionCreateStruct $createStruct) {
            $createStruct->position = 50;
        });
        $contentTypeObject->addFieldDefinitionObject($fieldDefinitionIdentifier, $fieldDefinition);

        $this->adapter->send(new Request(array(
            $contentTypeObject,
        )));

        $contentType = static::$repository->getContentTypeService()->loadContentTypeByRemoteId($remoteId);
        $fieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier);

        $this->assertEquals(50, $fieldDefinition->position);
    }
}
