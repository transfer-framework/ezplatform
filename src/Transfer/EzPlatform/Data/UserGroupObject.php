<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use Transfer\EzPlatform\Repository\Mapper\UserGroupMapper;

/*

** Available keys: **

    $data = [
        remote_id               => string
        parent_id               => int      // Defaults to 12
        main_language_code      => string   // Defaults to eng-GB
        content_type_identifier => string   // Defaults to user_group
        fields                  => FieldDefinition[] {@link see ContentObject and FieldDefinitionObject}
    ],
    $properties = [
        id                      => int (same as contentInfo->id)
        content_info            => \eZ\Publish\API\Repository\Values\Content\ContentInfo
        version_info            => \eZ\Publish\API\Repository\Values\Content\VersionInfo
        action                  => int {@link see \Transfer\EzPlatform\Data\Action\Enum\Action}
    ]


** Required on `create`:
**** Required by transfer:
    Fields in `fields` marked as required by ContentType

**** Required by eZ:
    parent_id
    content_type_identifier
    language
    Atleast one field defined in Fields


** Required on `update`:
**** Required by transfer:
    `id`
    Same as Content except for the defaults above

**** Required by eZ:
    id
    Same as Content except for the defaults above

*/

/**
 * User Group object.
 */
class UserGroupObject extends EzPlatformObject
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
