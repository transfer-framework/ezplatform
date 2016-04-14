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
     * @param mixed|Location $data
     * @param array $properties
     */
    public function __construct($data, array $properties = [])
    {
        if($data instanceof Location) {
            $this->getMapper()->locationToObject($data);
        }else {
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
