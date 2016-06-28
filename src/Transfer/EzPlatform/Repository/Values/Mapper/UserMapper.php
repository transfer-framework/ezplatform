<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values\Mapper;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use Transfer\EzPlatform\Repository\Values\UserObject;

/**
 * User mapper.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class UserMapper
{
    /**
     * @var UserObject
     */
    public $userObject;

    /**
     * @param UserObject $userObject
     */
    public function __construct(UserObject $userObject)
    {
        $this->userObject = $userObject;
    }

    /**
     * @param User $user
     */
    public function userToObject(User $user)
    {
        $this->userObject->data['username'] = $user->login;
        $this->userObject->data['email'] = $user->email;
        $this->userObject->data['enabled'] = $user->enabled;
        $this->userObject->data['max_login'] = $user->maxLogin;
    }

    /**
     * @param UserCreateStruct $createStruct
     */
    public function mapObjectToCreateStruct(UserCreateStruct $createStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'enabled' => 'enabled',
        );

        $this->arrayToStruct($createStruct, $keys);

        $this->assignStructFieldValues($createStruct);

        $this->callStruct($createStruct);
    }

    /**
     * @param UserUpdateStruct $updateStruct
     */
    public function mapObjectToUpdateStruct(UserUpdateStruct $updateStruct)
    {
        $updateStruct->contentUpdateStruct = new ContentUpdateStruct();

        // Name collection (ez => transfer)
        $keys = array(
            'email' => 'email',
            'maxLogin' => 'max_login',
            'enabled' => 'enabled',
        );

        $this->arrayToStruct($updateStruct, $keys);

        $this->assignStructFieldValues($updateStruct);

        $this->callStruct($updateStruct);
    }

    /**
     * @param UserCreateStruct|UserUpdateStruct $struct
     * @param array                             $keys
     */
    private function arrayToStruct($struct, $keys)
    {
        foreach ($keys as $ezKey => $transferKey) {
            if (isset($this->userObject->data[$transferKey])) {
                $struct->$ezKey = $this->userObject->data[$transferKey];
            }
        }
    }

    /**
     * @param UserCreateStruct|UserUpdateStruct $struct
     */
    private function assignStructFieldValues($struct)
    {
        foreach ($this->userObject->data['fields'] as $key => $value) {
            if ($struct instanceof UserUpdateStruct) {
                $struct->contentUpdateStruct->setField($key, $value);
            } else {
                $struct->setField($key, $value);
            }
        }
    }

    /**
     * @param UserCreateStruct|UserUpdateStruct $struct
     */
    private function callStruct($struct)
    {
        if ($this->userObject->getProperty('struct_callback')) {
            $callback = $this->userObject->getProperty('struct_callback');
            $callback($struct);
        }
    }
}
