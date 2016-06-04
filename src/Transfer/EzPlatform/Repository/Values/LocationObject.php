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

/**
 * Location object.
 *
 * @see http://transfer-framework.com/docs/1.0/sources_and_targets/ezplatform/the_objects/locationobject.html
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
