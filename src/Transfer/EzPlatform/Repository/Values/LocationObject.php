<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values;

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\EzPlatform\Repository\Values\Mapper\LocationMapper;

/*
  
** Available keys: **
 
    $data = [
        content_id          => int
        remote_id           => string
        parent_location_id  => int
        hidden              => bool
        priority            => int
        sort_field          => int 1-12 Location::SORT_FIELD_*
        sort_order          => int 0-1  Location::SORT_ORDER_DESC/SORT_ORDER_ASC
    ],
    $properties = [
        id                  => int
        depth               => int
        content_info        => \eZ\Publish\API\Repository\Values\Content\ContentInfo
        invisible           => bool
        path                => array
        path_string         => string
        action              => int {@link see \Transfer\EzPlatform\Data\Action\Enum\Action}
    ]


** Required on `create`:

**** Required by Transfer:
    * content_id
    * parent_location_id

**** Required by eZ:
    * @todo finish requirements


** Required on `update`:

**** Required by Transfer:
    * content_id or remote_id
    * parent_location_id

**** Required by eZ:
    * @todo finish requirements

*/

/**
 * Location object.
 */
class LocationObject extends EzPlatformObject
{
    /**
     * @var LocationMapper
     */
    private $mapper;

    /**
     * LocationObject constructor.
     *
     * @param array|Location $data
     * @param array          $properties
     */
    public function __construct($data, array $properties = [])
    {
        if ($data instanceof Location) {
            $this->getMapper()->locationToObject($data);
        } else {
            parent::__construct($data, $properties);
        }
    }

    /**
     * @return LocationMapper
     */
    public function getMapper()
    {
        if (!$this->mapper) {
            $this->mapper = new LocationMapper($this);
        }

        return $this->mapper;
    }
}
