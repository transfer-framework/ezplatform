<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values\Mapper;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use Transfer\EzPlatform\Repository\Values\LocationObject;

/**
 * User mapper.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class LocationMapper
{
    /**
     * @var LocationObject
     */
    public $locationObject;

    /**
     * @param LocationObject $locationObject
     */
    public function __construct(LocationObject $locationObject)
    {
        $this->locationObject = $locationObject;
    }

    public function locationToObject(Location $location)
    {
        $this->locationObject->data['content_id'] = $location->contentId;
        $this->locationObject->data['remote_id'] = $location->remoteId;
        $this->locationObject->data['parent_location_id'] = $location->parentLocationId;
        $this->locationObject->data['hidden'] = $location->hidden;
        $this->locationObject->data['priority'] = $location->priority;
        $this->locationObject->data['sort_field'] = $location->sortField;
        $this->locationObject->data['sort_order'] = $location->sortOrder;

        $this->locationObject->setProperty('id', $location->id);
        $this->locationObject->setProperty('depth', $location->depth);
        $this->locationObject->setProperty('content_info', $location->contentInfo);
        $this->locationObject->setProperty('invisible', $location->invisible);
        $this->locationObject->setProperty('path', $location->path);
        $this->locationObject->setProperty('path_string', $location->pathString);
    }

    /**
     * @param LocationCreateStruct $locationCreateStruct
     */
    public function getNewLocationCreateStruct(LocationCreateStruct $locationCreateStruct)
    {
        if (isset($this->locationObject->data['remote_id'])) {
            $locationCreateStruct->remoteId = $this->locationObject->data['remote_id'];
        }

        if (isset($this->locationObject->data['hidden'])) {
            $locationCreateStruct->hidden = $this->locationObject->data['hidden'];
        }

        if (isset($this->locationObject->data['priority'])) {
            $locationCreateStruct->priority = $this->locationObject->data['priority'];
        }

        if (isset($this->locationObject->data['sort_field'])) {
            $locationCreateStruct->sortField = $this->locationObject->data['sort_field'];
        }

        if (isset($this->locationObject->data['sort_order'])) {
            $locationCreateStruct->sortOrder = $this->locationObject->data['sort_order'];
        }

        $this->assignStructValues($this->locationObject, $locationCreateStruct);
    }

    /**
     * @param LocationUpdateStruct $locationUpdateStruct
     */
    public function getNewLocationUpdateStruct(LocationUpdateStruct $locationUpdateStruct)
    {
        if (isset($this->locationObject->data['remote_id'])) {
            $locationUpdateStruct->remoteId = $this->locationObject->data['remote_id'];
        }

        if (isset($this->locationObject->data['priority'])) {
            $locationUpdateStruct->priority = $this->locationObject->data['priority'];
        }

        if (isset($this->locationObject->data['sort_field'])) {
            $locationUpdateStruct->sortField = $this->locationObject->data['sort_field'];
        }

        if (isset($this->locationObject->data['sort_order'])) {
            $locationUpdateStruct->sortOrder = $this->locationObject->data['sort_order'];
        }

        $this->assignStructValues($this->locationObject, $locationUpdateStruct);
    }

    /**
     * @param LocationObject                            $object
     * @param LocationCreateStruct|LocationUpdateStruct $struct
     */
    private function assignStructValues(LocationObject $object, $struct)
    {
        if ($object->getProperty('struct_callback')) {
            $callback = $object->getProperty('struct_callback');
            $callback($struct);
        }
    }
}
