<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\testcase;

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\EzPlatform\Repository\Values\LocationObject;

class LocationTestCase extends ContentTestCase
{
    /**
     * initial structure:
     *  - location 2, content 57:
     *      - location 60, content 58.
     *
     *      // After setup:
     *      - location 62, content 61
     *      - location 64, content 63
     */
    protected $_test_content_id_1;
    protected $_test_content_id_2;

    protected $_test_location_id_1;
    protected $_test_location_id_2;

    protected $_test_location_remote_id_1 = 'test_integration_location_1';
    protected $_test_location_remote_id_2 = 'test_integration_location_2';
    protected $_test_location_remote_id_3 = 'test_integration_location_3';

    protected $_test_content_remote_id_1 = 'test_integration_content_1';
    protected $_test_content_remote_id_2 = 'test_integration_content_2';
    protected $_test_content_remote_id_3 = 'test_integration_content_3';

    public function setUp()
    {
        parent::setUp();

        // requesites
        $this->setUpContents();
        $this->setUpLocations();
    }

    protected function setUpContents()
    {
        // First one
        $contentObject = $this->getContentObject(array(
            'title' => 'Test title',
        ), $this->_test_location_remote_id_1, static::_content_type_article);

        $co = static::$contentManager->createOrUpdate($contentObject);
        $this->_test_content_id_1 = $co->getProperty('content_info')->id;

        // Another one
        $contentObject = $this->getContentObject(array(
            'title' => 'Test title',
        ), $this->_test_location_remote_id_2, static::_content_type_article);

        $co = static::$contentManager->createOrUpdate($contentObject);
        $this->_test_content_id_2 = $co->getProperty('content_info')->id;
    }

    protected function setUpLocations()
    {
        // First one
        $locationObject = $this->getLocationObject($this->_test_location_remote_id_1, $this->_test_content_id_1, 60);
        $co = static::$locationManager->createOrUpdate($locationObject);
        $this->_test_locationId_0 = $co->getProperty('id');

        // Another one
        $locationObject = $this->getLocationObject($this->_test_location_remote_id_2, $this->_test_content_id_2, 63);
        $co = static::$locationManager->createOrUpdate($locationObject);
        $this->_test_locationId_1 = $co->getProperty('id');
    }

    protected function getLocationObject($remote_id, $contentId = false, $parentLocationId = false)
    {
        $locationObject = new LocationObject(array(
            'remote_id' => $remote_id,
        ));

        if ($contentId) {
            $locationObject->data['content_id'] = $contentId;
        }
        if ($parentLocationId) {
            $locationObject->data['parent_location_id'] = $parentLocationId;
        }

        $locationObject->data['hidden'] = false;
        $locationObject->data['priority'] = 1;
        $locationObject->data['sort_field'] = Location::SORT_FIELD_PRIORITY;
        $locationObject->data['sort_order'] = Location::SORT_ORDER_DESC;

        return $locationObject;
    }
}
