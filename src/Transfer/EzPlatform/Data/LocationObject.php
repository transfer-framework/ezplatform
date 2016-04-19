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
use Transfer\EzPlatform\Repository\Mapper\LocationMapper;

/*
  
** Available keys: **
 
    $data = [
        id                  => int
        content_id          => int          Required on `create`
        remote_id           => string
        parent_location_id  => int          Required on `create`
        depth               => int
        hidden              => bool
        priority            => int
        sort_field          => int 1-12 Location::SORT_FIELD_*
        sort_order          => int 0-1  Location::SORT_ORDER_DESC/SORT_ORDER_ASC
    ],
    $properties = [
        content_info        => \eZ\Publish\API\Repository\Values\Content\ContentInfo
        invisible           => bool
        path                => array
        path_string         => string
    ]


** Required on `create`:

    Both `content_id` and `parent_location_id`


** Required on `update`:

    One of `content_id` or `remote_id` must be present

*/

/**
 * Location object.
 */
class LocationObject extends ValueObject
{
    /**
     * @var LocationMapper
     */
    private $mapper;

    /**
     * LocationObject constructor.
     *
     * @param mixed|Location $data
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
