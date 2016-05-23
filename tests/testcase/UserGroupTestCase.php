<?php

namespace Transfer\EzPlatform\tests\testcase;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;

class UserGroupTestCase extends EzPlatformTestCase
{
    protected $main_usergroup_id = 12;
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
     * @param array $fields
     *
     * @return UserGroupObject
     */
    protected function getUsergroup(array $fields, $parentId = false)
    {
        if (!$parentId) {
            $parentId = $this->main_usergroup_id;
        }

        return new UserGroupObject(array(
            'parent_id' => $parentId,
            'content_type_identifier' => $this->contentype_identifier,
            'main_language_code' => $this->main_language_code,
            'fields' => $fields,
        ));
    }

    /**
     * @return UserGroup
     */
    protected function getRootUserGroup()
    {
        return static::$repository->getUserService()->loadUserGroup($this->main_usergroup_id);
    }
}
