<?php

/**
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
     * @param LocationCreateStruct $createStruct
     */
    public function mapObjectToCreateStruct(LocationCreateStruct $createStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'remoteId' => 'remote_id',
            'hidden' => 'hidden',
            'priority' => 'priority',
            'sortField' => 'sort_field',
            'sortOrder' => 'sort_order',
        );

        $this->arrayToStruct($createStruct, $keys);

        $this->callStruct($createStruct);
    }

    /**
     * @param LocationUpdateStruct $updateStruct
     */
    public function mapObjectToUpdateStruct(LocationUpdateStruct $updateStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'remoteId' => 'remote_id',
            'priority' => 'priority',
            'sortField' => 'sort_field',
            'sortOrder' => 'sort_order',
        );

        $this->arrayToStruct($updateStruct, $keys);

        $this->callStruct($updateStruct);
    }

    /**
     * @param LocationCreateStruct|LocationUpdateStruct $struct
     * @param array                                     $keys
     */
    private function arrayToStruct($struct, $keys)
    {
        foreach ($keys as $ezKey => $transferKey) {
            if (isset($this->locationObject->data[$transferKey])) {
                $struct->$ezKey = $this->locationObject->data[$transferKey];
            }
        }
    }

    /**
     * @param LocationCreateStruct|LocationUpdateStruct $struct
     */
    private function callStruct($struct)
    {
        if ($this->locationObject->getProperty('struct_callback')) {
            $callback = $this->locationObject->getProperty('struct_callback');
            $callback($struct);
        }
    }
}
