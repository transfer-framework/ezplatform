<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Content;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use Transfer\EzPlatform\Data\ContentTypeObject;

/**
 * Contenttype mapper.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class ContentTypeMapper
{
    /**
     * @var ContentTypeObject
     */
    public $contentTypeObject;

    /**
     * @var ContentTypeService
     */
    public $contentTypeService;

    /**
     * @param ContentTypeObject $contentTypeObject
     */
    public function __construct(ContentTypeObject $contentTypeObject)
    {
        $this->contentTypeObject = &$contentTypeObject;
    }

    /**
     * @param ContentTypeCreateStruct $contentTypeCreateStruct
     */
    public function fillContentTypeCreateStruct(ContentTypeCreateStruct $contentTypeCreateStruct)
    {
        $contentTypeCreateStruct->names = $this->contentTypeObject->data['names'];
        $contentTypeCreateStruct->descriptions = $this->contentTypeObject->data['descriptions'];
        $contentTypeCreateStruct->remoteId = sha1(microtime());
        $contentTypeCreateStruct->mainLanguageCode = $this->contentTypeObject->data['main_language_code'];
        $contentTypeCreateStruct->nameSchema = $this->contentTypeObject->data['name_schema'];
        $contentTypeCreateStruct->urlAliasSchema = $this->contentTypeObject->data['url_alias_schema'];
        $contentTypeCreateStruct->isContainer = $this->contentTypeObject->data['is_container'];
        $contentTypeCreateStruct->defaultAlwaysAvailable = $this->contentTypeObject->data['default_always_available'];
        $contentTypeCreateStruct->defaultSortField = $this->contentTypeObject->data['default_sort_field'];
        $contentTypeCreateStruct->defaultSortOrder = $this->contentTypeObject->data['default_sort_order'];
    }

    /**
     * @param ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function fillContentTypeUpdateStruct(ContentTypeUpdateStruct $contentTypeUpdateStruct)
    {
        $contentTypeUpdateStruct->names = $this->contentTypeObject->data['names'];
        $contentTypeUpdateStruct->descriptions = $this->contentTypeObject->data['descriptions'];
        $contentTypeUpdateStruct->mainLanguageCode = $this->contentTypeObject->data['main_language_code'];
        $contentTypeUpdateStruct->nameSchema = $this->contentTypeObject->data['name_schema'];
        $contentTypeUpdateStruct->urlAliasSchema = $this->contentTypeObject->data['url_alias_schema'];
        $contentTypeUpdateStruct->isContainer = $this->contentTypeObject->data['is_container'];
        $contentTypeUpdateStruct->defaultAlwaysAvailable = $this->contentTypeObject->data['default_always_available'];
        $contentTypeUpdateStruct->defaultSortField = $this->contentTypeObject->data['default_sort_field'];
        $contentTypeUpdateStruct->defaultSortOrder = $this->contentTypeObject->data['default_sort_order'];
    }
}
