<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Repository\Values;

use Transfer\EzPlatform\Repository\Values\Mapper\UserMapper;

/*

** Available keys: **

    $parents = Transfer\EzPlatform\Data\UserGroupObject[]
    $data = [
        username => string
        email => string
        password => string
        main_language_code => string
        enabled => bool
        max_login => int
        fields => [ first_name => string
                    last_name => string
                    ...                 ]
    ],
    $properties = [
        action => int {@link see \Transfer\EzPlatform\Data\Action\Enum\Action}
    ]


** Required on `create`:
**** Required by transfer:
    `username´
    `email`
    `password`

**** Required by eZ:
    `username´
    `èmail`
    `password`
    `main_language_code`
    And any required fields in `fields`

** Required on `update`:
**** Required by transfer:
    `username`

**** Required by eZ:
    `username`

*/

/**
 * User object.
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
