<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Repository\Content\UserGroupMapper;

/**
 * User Group object.
 */
class UserGroupObject extends ValueObject
{
    /**
     * @var UserGroupMapper
     */
    private $mapper;

    /**
     * UserGroupObject constructor.
     *
     * @param mixed $data
     * @param array $properties
     */
    public function __construct(array $data, array $properties = array())
    {
        if (!isset($data['parent_id'])) {
            $data['parent_id'] = 12;
        }
        if (!isset($data['main_language_code'])) {
            $data['main_language_code'] = 'eng-GB';
        }
        if (!isset($data['content_type_identifier'])) {
            $data['content_type_identifier'] = 'user_group';
        }
        parent::__construct($data, $properties);
    }

    /**
     * @return UserGroupMapper
     */
    public function getMapper()
    {
        if (!$this->mapper) {
            $this->mapper = new UserGroupMapper($this);
        }

        return $this->mapper;
    }
}
