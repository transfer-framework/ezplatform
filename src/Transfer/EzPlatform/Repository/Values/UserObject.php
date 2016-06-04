<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values;

use Transfer\EzPlatform\Repository\Values\Mapper\UserMapper;

/**
 * User object.
 *
 * @see http://transfer-framework.com/docs/1.0/sources_and_targets/ezplatform/the_objects/userobject.html
 */
class UserObject extends EzPlatformObject
{
    /**
     * @var UserGroupObject[]
     */
    public $parents;

    /**
     * @var UserMapper
     */
    protected $mapper;

    public function __construct(array $data, array $properties = array())
    {
        if (isset($data['parents'])) {
            $this->parents = $data['parents'];
            unset($data['parents']);
        }

        $data['max_login'] = isset($data['max_login']) ? $data['max_login'] : null;

        parent::__construct($data, $properties);
    }

    /**
     * @return UserMapper
     */
    public function getMapper()
    {
        if (!$this->mapper) {
            $this->mapper = new UserMapper($this);
        }

        return $this->mapper;
    }
}
