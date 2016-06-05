<?php

/**
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
     * @param ContentCreateStruct $createStruct
     *
     * @throws \InvalidArgumentException
     */
    public function mapObjectToCreateStruct(ContentCreateStruct $createStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'mainLanguageCode' => 'main_language_code',
            'remoteId' => 'remote_id',
        );

        $this->arrayToStruct($createStruct, $keys);

        $this->assignStructFieldValues($createStruct);

        $this->callStruct($createStruct);
    }

    /**
     * @param ContentUpdateStruct $updateStruct
     */
    public function mapObjectToUpdateStruct(ContentUpdateStruct $updateStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'creatorId' => 'creator_id',
        );

        $this->arrayToStruct($updateStruct, $keys);

        $this->assignStructFieldValues($updateStruct);

        $this->callStruct($updateStruct);
    }

    /**
     * @param ContentCreateStruct|ContentUpdateStruct $struct
     */
    private function assignStructFieldValues($struct)
    {
        foreach ($this->contentObject->data as $key => $value) {
            if (is_array($value)) {
                $value = end($value);
            }

            $struct->setField($key, $value);
        }
    }

    /**
     * @param ContentCreateStruct|ContentUpdateStruct $struct
     * @param array                                   $keys
     */
    private function arrayToStruct($struct, $keys)
    {
        foreach ($keys as $ezKey => $transferKey) {
            if ($this->contentObject->getProperty($transferKey)) {
                $struct->$ezKey = $this->contentObject->getProperty($transferKey);
            }
        }
    }

    /**
     * @param ContentCreateStruct|ContentUpdateStruct $struct
     */
    private function callStruct($struct)
    {
        if ($this->contentObject->getProperty('struct_callback')) {
            $callback = $this->contentObject->getProperty('struct_callback');
            $callback($struct);
        }
    }
}
