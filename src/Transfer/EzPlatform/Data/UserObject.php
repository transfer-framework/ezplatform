<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Repository\Content\UserMapper;

/**
 * User object.
 */
class UserObject extends ValueObject
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
