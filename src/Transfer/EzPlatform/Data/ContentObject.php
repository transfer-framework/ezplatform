<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\InvalidDataStructureException;

/**
 * Content object.
 */
class ContentObject extends ValueObject
{
    /**
     * Constructs content object.
     *
     * @param array $data       Field data
     * @param array $properties Additional properties
     */
    public function __construct(array $data, array $properties = array())
    {
        parent::__construct($data, array_merge(
            $properties,
            array('main_object' => true)
        ));

        if (isset($properties['parent_locations'])) {
            $this->properties['parent_locations'] = [];
            array_walk($properties['parent_locations'], function ($location) {
                $this->addParentLocation($location);
            });
        }
    }

    /**
     * Sets content type identifier.
     *
     * @param string $type Content type identifier
     */
    public function setContentType($type)
    {
        $this->setProperty('content_type_identifier', $type);
    }

    /**
     * Sets language.
     *
     * @param string $language Language
     */
    public function setLanguage($language)
    {
        $this->setProperty('language', $language);
    }

    /**
     * Sets priority.
     *
     * @param int $priority Priority
     */
    public function setPriority($priority)
    {
        $this->setProperty('priority', (int) $priority);
    }

    /**
     * Returns priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return (int) $this->getProperty('priority');
    }

    /**
     * Sets remote ID.
     *
     * @param string $remoteId Remote ID
     */
    public function setRemoteId($remoteId)
    {
        $this->setProperty('remote_id', $remoteId);
    }

    /**
     * Returns remote ID.
     *
     * @return string
     */
    public function getRemoteId()
    {
        return $this->getProperty('remote_id');
    }

    /**
     * Sets version info.
     *
     * @param VersionInfo $versionInfo Version info
     */
    public function setVersionInfo($versionInfo)
    {
        $this->setProperty('version_info', $versionInfo);
    }

    /**
     * Sets content info.
     *
     * @param ContentInfo $contentInfo Content info.
     */
    public function setContentInfo($contentInfo)
    {
        $this->setProperty('content_info', $contentInfo);
    }

    /**
     * Returns content info.
     *
     * @return ContentInfo
     */
    public function getContentInfo()
    {
        return $this->getProperty('content_info');
    }

    /**
     * Sets main location ID.
     *
     * @param int $id Main location ID
     */
    public function setMainLocationId($id)
    {
        $this->setProperty('main_location_id', $id);
    }

    /**
     * Sets content object as hidden.
     *
     * @param bool $hidden
     */
    public function setHidden($hidden)
    {
        $this->setProperty('hidden', (boolean) $hidden);
    }

    /**
     * Returns visibility state (hidden or visible).
     *
     * @return bool
     */
    public function isHidden()
    {
        return (boolean) $this->getProperty('hidden');
    }

    /**
     * Sets as main object.
     *
     * If true, this object is going to be the main object among all others having the same remote id.
     *
     * @param bool $mainObject Whether this object is the main object
     */
    public function setMainObject($mainObject)
    {
        $this->setProperty('main_object', (boolean) $mainObject);
    }

    /**
     * Returns main object state.
     *
     * @return bool True, if object is the main object.
     */
    public function isMainObject()
    {
        return (boolean) $this->getProperty('main_object');
    }

    /**
     * Convert parameters to LocationCreateStruct and stores it on the ContentObject.
     *
     * @param LocationCreateStruct|LocationObject|int $parentLocation
     */
    public function addParentLocation($parentLocation)
    {
        if ($parentLocation instanceof LocationCreateStruct) {
            $locationCreateStruct = $parentLocation;
        } else {
            $locationCreateStruct = $this->convertToLocationCreateStruct($parentLocation);
        }

        if ((int) $locationCreateStruct->parentLocationId < 2) {
            throw new InvalidDataStructureException('Location id must be an integer of 2 or above.');
        }

        $this->properties['parent_locations'][$locationCreateStruct->parentLocationId] = $locationCreateStruct;
    }

    /**
     * Accepts parameter as int, Location or LocationObject.
     *
     * @param $parentLocation
     *
     * @return LocationCreateStruct
     */
    private function convertToLocationCreateStruct($parentLocation)
    {
        $locationCreateStruct = new LocationCreateStruct();
        if ($parentLocation instanceof Location) {
            $locationCreateStruct->parentLocationId = $parentLocation->parentLocationId;
            $locationCreateStruct->hidden = $parentLocation->hidden;
            $locationCreateStruct->priority = $parentLocation->priority;
            $locationCreateStruct->sortField = $parentLocation->sortField;
            $locationCreateStruct->sortOrder = $parentLocation->sortOrder;
            $locationCreateStruct->remoteId = $parentLocation->remoteId;
        } elseif ($parentLocation instanceof LocationObject) {
            $locationCreateStruct->parentLocationId = $parentLocation->data['parent_id'];
        } elseif (is_int($parentLocation)) {
            $locationCreateStruct->parentLocationId = $parentLocation;
        }

        return $locationCreateStruct;
    }

    /**
     * @return array
     */
    public function getParentLocations()
    {
        return (array) $this->getProperty('parent_locations');
    }
}
