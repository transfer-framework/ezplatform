<?php

namespace Transfer\EzPlatform\tests\testcase;

use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\LocationObject;

class LocationTestCase extends ContentTestCase
{
    /**
     * initial structure:
     *  - location 2, content 57:
     *      - location 60, content 58
     *
     *      // After setup:
     *      - location 62, content 61
     *      - location 64, content 63
     */


    protected $_test_contentId_0;
    protected $_test_contentId_1;

    protected $_test_locationId_0;
    protected $_test_locationId_1;

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
        ), '_integration_location_content_0_0', static::_content_type_article);

        $co = static::$contentManager->createOrUpdate($contentObject);
        $this->_test_contentId_0 = $co->getProperty('content_info')->id;

        // Another one
        $contentObject = $this->getContentObject(array(
            'title' => 'Test title',
        ), '_integration_location_content_0_1', static::_content_type_article);

        $co = static::$contentManager->createOrUpdate($contentObject);
        $this->_test_contentId_1 = $co->getProperty('content_info')->id;

    }

    protected function setUpLocations()
    {

        // First one
        $locationObject = $this->getLocationObject('_integration_location_location_0_0', $this->_test_contentId_0, 60);
        $co = static::$locationManager->createOrUpdate($locationObject);
        $this->_test_locationId_0 = $co->getProperty('id');

        // Another one
        $locationObject = $this->getLocationObject('_integration_location_location_0_1', $this->_test_contentId_1, 62);
        $co = static::$locationManager->createOrUpdate($locationObject);
        $this->_test_locationId_1 = $co->getProperty('id');
    }

    protected function getLocationObject($remote_id, $contentId = false, $parentLocationId = false)
    {
        $locationObject = new LocationObject(array(
            'remote_id' => $remote_id,
        ));

        if($contentId) {
            $locationObject->data['content_id'] = $contentId;
        }
        if($parentLocationId) {
            $locationObject->data['parent_location_id'] = $parentLocationId;
        }

        return $locationObject;
    }

}