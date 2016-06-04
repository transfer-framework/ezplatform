<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\testcase;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;

class UserGroupTestCase extends EzPlatformTestCase
{
    protected $main_usergroup_id = 12;
    protected $main_usergroup_remote_id = 'f5c88a2209584891056f987fd965b0ba';
    protected $main_language_code = 'eng-GB';
    protected $contentype_identifier = 'user_group';

    protected $_to_be_deleted_usergroup_id;

    /**
     * @var EzPlatformAdapter
     */
    public $adapter;

    public function setUp()
    {
        $this->adapter = new EzPlatformAdapter(array(
            'repository' => static::$repository,
        ));
        $this->adapter->setLogger(
            $this->getMock(LoggerInterface::class)
        );

        $this->setUpUserGroups();
    }

    protected function setUpUserGroups()
    {
        // UserGroup to be deleted
        $userGroupObjectToBeDeleted = $this->getUsergroup(array(
            'name' => 'To be deleted usergroup',
        ));
        $userGroupToBeDeleted = static::$userGroupManager->createOrUpdate($userGroupObjectToBeDeleted);
        $this->_to_be_deleted_usergroup_id = $userGroupToBeDeleted->getProperty('id');
    }

    /**
     * @param array       $fields
     * @param bool|int    $parentId
     * @param bool|string $remote_id
     *
     * @return UserGroupObject
     */
    protected function getUsergroup(array $fields, $parentId = false, $remote_id = false)
    {
        $data = array(
            'content_type_identifier' => $this->contentype_identifier,
            'main_language_code' => $this->main_language_code,
            'fields' => $fields,
        );

        if ($parentId) {
            $data['parent_id'] = $parentId;
        }

        if ($remote_id) {
            $data['remote_id'] = $remote_id;
        }

        return new UserGroupObject($data);
    }

    /**
     * @return UserGroup
     */
    protected function getRootUserGroup()
    {
        return static::$repository->getUserService()->loadUserGroup($this->main_usergroup_id);
    }
}
