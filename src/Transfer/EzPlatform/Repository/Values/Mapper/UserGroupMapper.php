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

    /**
     * @param UserGroup $userGroup
     */
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
     * @param UserGroupCreateStruct $createStruct
     */
    public function mapObjectToCreateStruct(UserGroupCreateStruct $createStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'remoteId' => 'remote_id',
        );

        $this->arrayToStruct($createStruct, $keys);

        $this->assignStructFieldValues($createStruct);

        $this->callStruct($createStruct);
    }

    /**
     * @param UserGroupUpdateStruct $updateStruct
     */
    public function mapObjectToUpdateStruct(UserGroupUpdateStruct $updateStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'remoteId' => 'remote_id',
        );

        $this->arrayToStruct($updateStruct, $keys);

        $this->assignStructFieldValues($updateStruct);

        $this->callStruct($updateStruct);
    }

    /**
     * @param UserGroupCreateStruct $struct Struct to assign values to
     */
    private function assignStructFieldValues($struct)
    {
        foreach ($this->userGroupObject->data['fields'] as $key => $value) {
            if ($struct instanceof UserGroupUpdateStruct) {
                $struct->contentUpdateStruct->setField($key, $value);
            } else {
                $struct->setField($key, $value);
            }
        }
    }

    /**
     * @param UserGroupCreateStruct|UserGroupUpdateStruct $struct
     * @param array                                       $keys
     */
    private function arrayToStruct($struct, $keys)
    {
        foreach ($keys as $ezKey => $transferKey) {
            if (isset($this->userGroupObject->data[$transferKey])) {
                if ($struct instanceof UserGroupUpdateStruct) {
                    if (!$struct->contentMetadataUpdateStruct) {
                        $struct->contentMetadataUpdateStruct = new ContentMetadataUpdateStruct();
                    }
                    $struct->contentMetadataUpdateStruct->$ezKey = $this->userGroupObject->data[$transferKey];
                } else {
                    $struct->$ezKey = $this->userGroupObject->data[$transferKey];
                }
            }
        }
    }

    /**
     * @param UserGroupCreateStruct|UserGroupUpdateStruct $struct
     */
    private function callStruct($struct)
    {
        if ($this->userGroupObject->getProperty('struct_callback')) {
            $callback = $this->userGroupObject->getProperty('struct_callback');
            $callback($struct);
        }
    }
}
