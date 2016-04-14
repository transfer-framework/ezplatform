<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\InvalidDataStructureException;
use Transfer\EzPlatform\Repository\Mapper\ContentMapper;

/**
 * Content object.
 */
class ContentObject extends ValueObject
{
    /**
     * @var ContentMapper
     */
    private $mapper;

    /**
     * @return ContentMapper
     */
    public function getMapper()
    {
        if (!$this->mapper) {
            $this->mapper = new ContentMapper($this);
        }

        return $this->mapper;
    }

    /**
     * Constructs content object.
     *
     * @param array $data       Field data
     * @param array $properties Additional properties
     */
    public function __construct(array $data, array $properties = array())
    {
        parent::__construct($data, array_merge(
            array(
                'main_object' => true,
                'parent_locations' => [],
            ),
            $properties
        ));

        if (isset($properties['parent_locations'])) {
            $this->setParentLocations($properties['parent_locations']);
        }
    }

    /**
     * Values in array must be of type Location, LocationObject or int
     *
     * @param array $parentLocations
     */
    public function setParentLocations(array $parentLocations)
    {
        $this->properties['parent_locations'] = [];
        foreach($parentLocations as $location) {
            $this->addParentLocation($location);
        }
    }

    /**
     * Convert parameters to LocationCreateStruct and stores it on the ContentObject.
     *
     * @param Location|LocationObject|int $parentLocation
     *
     * @throws InvalidDataStructureException
     */
    public function addParentLocation($parentLocation)
    {
        $locationObject = $this->convertToLocationObject($parentLocation);

        if (!isset($locationObject->data['parent_location_id']) || (int) $locationObject->data['parent_location_id'] < 1) {
            echo print_r($locationObject);
            throw new InvalidDataStructureException('Parent location id must be an integer of 2 or above.');
        }

        if(!isset($locationObject->data['content_id'])) {
            if(isset($this->data['id'])) {
                $locationObject->data['content_id'] = $this->data['id'];
            }
        }

        $this->properties['parent_locations'][$locationObject->data['parent_location_id']] = $locationObject;
    }

    /**
     * @param int|Location|LocationObject $parentLocation
     *
     * @return LocationObject
     */
    private function convertToLocationObject($parentLocation)
    {
        $locationObject = new LocationObject(array());

        switch(true) {
            case $parentLocation instanceof Location:
                $locationObject->getMapper()->locationToObject($parentLocation);
                break;
            case $parentLocation instanceof LocationObject:
                $locationObject = $parentLocation;
                break;
            case is_int($parentLocation):
                $locationObject->data['parent_location_id'] = $parentLocation;
                break;
        }

        return $locationObject;
    }
}
