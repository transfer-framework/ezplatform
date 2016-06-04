<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values\Mapper;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use Transfer\EzPlatform\Repository\Values\ContentObject;

/**
 * User mapper.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class ContentMapper
{
    /**
     * @var ContentObject
     */
    public $contentObject;

    /**
     * @param ContentObject $contentObject
     */
    public function __construct(ContentObject $contentObject)
    {
        $this->contentObject = $contentObject;
    }

    /**
     * @param Content $content
     */
    public function contentToObject(Content $content)
    {
        $this->contentObject->setProperty('id', $content->id);
        $this->contentObject->setProperty('remote_id', $content->contentInfo->remoteId);
        $this->contentObject->setProperty('content_info', $content->contentInfo);
        $this->contentObject->setProperty('version_info', $content->versionInfo);
        foreach ($content->getFields() as $field) {
            $this->contentObject->data[$field->fieldDefIdentifier][$field->languageCode] = $field->value;
        }
    }

    /**
     * Maps object data to create struct.
     *
     * @param ContentObject       $object       Content object to map from
     * @param ContentCreateStruct $createStruct Content create struct to map to
     *
     * @throws \InvalidArgumentException
     */
    public function mapObjectToCreateStruct(ContentObject $object, ContentCreateStruct $createStruct)
    {
        if ($object->getProperty('language')) {
            $createStruct->mainLanguageCode = $object->getProperty('language');
        }

        if ($object->getProperty('remote_id')) {
            $createStruct->remoteId = $object->getProperty('remote_id');
        }

        $this->assignStructFieldValues($object, $createStruct);
    }

    /**
     * Maps object data to update struct.
     *
     * @param ContentObject       $object              Content object to map from
     * @param ContentUpdateStruct $contentUpdateStruct Content update struct to map to
     *
     * @throws \InvalidArgumentException
     */
    public function mapObjectToUpdateStruct(ContentObject $object, ContentUpdateStruct $contentUpdateStruct)
    {
        $this->assignStructFieldValues($object, $contentUpdateStruct);
    }

    /**
     * Copies content object data from a struct.
     *
     * @param ContentObject $object Content object to get values from
     * @param object        $struct Struct to assign values to
     */
    private function assignStructFieldValues(ContentObject $object, $struct)
    {
        foreach ($object->data as $key => $value) {
            if (is_array($value)) {
                $value = end($value);
            }

            $struct->setField($key, $value);
        }

        if ($object->getProperty('struct_callback')) {
            $callback = $object->getProperty('struct_callback');
            $callback($struct);
        }
    }
}
