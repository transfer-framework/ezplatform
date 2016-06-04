<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Repository\Values;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\EzPlatform\Exception\InvalidDataStructureException;
use Transfer\EzPlatform\Repository\Values\Mapper\ContentMapper;

/*

** Available keys: **

    $data = [
        my_first_field_identifier => my_first_field_value
        my_second_field_identifier => my_second_field_value
        ...
    ],
    $properties = [
        id                      => int
        name                    => string
        remote_id               => string
        content_info            => \eZ\Publish\API\Repository\Values\Content\ContentInfo
        version_info            => \eZ\Publish\API\Repository\Values\Content\VersionInfo
        main_object             => bool
        parent_locations        => int|Location|LocationObject
        content_type_identifier => string
        language                => string
        main_location_id        => int
        action                  => int {@link see \Transfer\EzPlatform\Data\Action\Enum\Action}
    ]


** Required on `create`:
**** Required by transfer:
    * content_type_identifier
    * $data with keys matching the required fields of the ContentType

**** Required by eZ:
    * `content_type_identifier`
    * `language`
    Fields matching the required fields of the ContentType

** Required on `update`:
**** Required by transfer:
    One of `id`, or `remote_id` must be present
    Atleast one fieldtype

**** Required by eZ:
    One of `id`, or `remote_id` must be present
    Atleast one fieldtype

*/

/**
 * Content object.
 */
class ContentObject extends EzPlatformObject
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
     * @param array|Content $data       Field data
     * @param array         $properties Additional properties
     */
    public function __construct($data, array $properties = array())
    {
        if ($data instanceof Content) {
            $this->getMapper()->contentToObject($data);
        } else {
            parent::__construct($data, array_merge(
                array(
                    'main_object' => true,
                    'parent_locations' => [],
                ),
                $properties
            ));
        }

        if (isset($properties['parent_locations'])) {
            $this->setParentLocations($properties['parent_locations']);
        }
    }

    /**
     * Values in array must be of type Location, LocationObject or int.
     *
     * @param array $parentLocations
     */
    public function setParentLocations(array $parentLocations)
    {
        $this->properties['parent_locations'] = [];
        foreach ($parentLocations as $location) {
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
            throw new InvalidDataStructureException('Parent location id must be an integer of 2 or above.');
        }

        if (!isset($locationObject->data['content_id'])) {
            if ($this->getProperty('id')) {
                $locationObject->data['content_id'] = $this->getProperty('id');
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

        switch (true) {
            case $parentLocation instanceof Location:
                $locationObject->getMapper()->locationToObject($parentLocation);
                break;
            case is_int($parentLocation):
                $locationObject->data['parent_location_id'] = $parentLocation;
                break;
            case $parentLocation instanceof LocationObject:
            default:
                $locationObject = $parentLocation;
        }

        return $locationObject;
    }
}
