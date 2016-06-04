<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Repository\Values\Mapper;

use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;

/**
 * Usergroup mapper.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class UserGroupMapper
{
    /**
     * @var UserGroupObject
     */
    public $userGroupObject;

    /**
     * @param UserGroupObject $userGroupObject
     */
    public function __construct(UserGroupObject $userGroupObject)
    {
        $this->userGroupObject = $userGroupObject;
    }

    public function userGroupToObject(UserGroup $userGroup)
    {
        $this->userGroupObject->data['parent_id'] = $userGroup->parentId;

        $this->userGroupObject->data['fields'] = [];
        foreach ($userGroup->getFields() as $field) {
            $this->userGroupObject->data['fields'][$field->fieldDefIdentifier] = $field->value->text;
        }

        $this->userGroupObject->setProperty('id', $userGroup->contentInfo->id);
        $this->userGroupObject->setProperty('content_info', $userGroup->contentInfo);
        $this->userGroupObject->setProperty('version_info', $userGroup->versionInfo);
    }

    /**
     * @param UserGroupCreateStruct $userGroupCreateStruct
     */
    public function populateUserGroupCreateStruct(UserGroupCreateStruct $userGroupCreateStruct)
    {
        if (isset($this->userGroupObject->data['remote_id'])) {
            $userGroupCreateStruct->remoteId = $this->userGroupObject->data['remote_id'];
        }

        $fields = array_flip($this->userGroupObject->data['fields']);
        array_walk($fields, array($userGroupCreateStruct, 'setField'));
    }

    /**
     * @param UserGroupUpdateStruct $userGroupUpdateStruct
     */
    public function populateUserGroupUpdateStruct(UserGroupUpdateStruct $userGroupUpdateStruct)
    {
        if (isset($this->userGroupObject->data['remote_id'])) {
            $userGroupUpdateStruct->contentMetadataUpdateStruct = new ContentMetadataUpdateStruct();
            $userGroupUpdateStruct->contentMetadataUpdateStruct->remoteId = $this->userGroupObject->data['remote_id'];
        }

        $fields = array_flip($this->userGroupObject->data['fields']);
        array_walk($fields, array($userGroupUpdateStruct->contentUpdateStruct, 'setField'));
    }
}
