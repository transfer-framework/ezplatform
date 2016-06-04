<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Repository\Values\Mapper;

use eZ\Publish\API\Repository\Values\Content\Content;
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
}
