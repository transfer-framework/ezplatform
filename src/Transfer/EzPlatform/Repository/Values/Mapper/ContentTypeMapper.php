<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values\Mapper;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\Core\REST\Client\Values\ContentType\ContentType as RESTContentType;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;

/**
 * Contenttype mapper.
 *
 * @internal
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
     * @param ContentType $contentType
     */
    public function contentTypeToObject(ContentType $contentType)
    {
        $this->contentTypeObject->data['identifier'] = $contentType->identifier;
        $this->contentTypeObject->data['names'] = $contentType->getNames();
        $this->contentTypeObject->data['descriptions'] = $contentType->getDescriptions();
        $this->contentTypeObject->data['name_schema'] = $contentType->nameSchema;
        $this->contentTypeObject->data['url_alias_schema'] = $contentType->urlAliasSchema;
        $this->contentTypeObject->data['is_container'] = $contentType->isContainer;
        $this->contentTypeObject->data['default_always_available'] = $contentType->defaultAlwaysAvailable;
        $this->contentTypeObject->data['default_sort_field'] = $contentType->defaultSortField;
        $this->contentTypeObject->data['default_sort_order'] = $contentType->defaultSortOrder;

        $this->contentTypeObject->setProperty('id', $contentType->id);
        $this->contentTypeObject->setProperty('content_type_groups', $contentType->contentTypeGroups);
    }

    /**
     * @param ContentTypeCreateStruct $contentTypeCreateStruct
     */
    public function fillContentTypeCreateStruct(ContentTypeCreateStruct $contentTypeCreateStruct)
    {
        $contentTypeCreateStruct->remoteId = sha1(microtime());

        if(isset($this->contentTypeObject->data['names'])) {
            $contentTypeCreateStruct->names = $this->contentTypeObject->data['names'];
        }
        if(isset($this->contentTypeObject->data['descriptions'])) {
            $contentTypeCreateStruct->descriptions = $this->contentTypeObject->data['descriptions'];
        }
        if(isset($this->contentTypeObject->data['main_language_code'])) {
            $contentTypeCreateStruct->mainLanguageCode = $this->contentTypeObject->data['main_language_code'];
        }
        if(isset($this->contentTypeObject->data['name_schema'])) {
            $contentTypeCreateStruct->nameSchema = $this->contentTypeObject->data['name_schema'];
        }
        if(isset($this->contentTypeObject->data['url_alias_schema'])) {
            $contentTypeCreateStruct->urlAliasSchema = $this->contentTypeObject->data['url_alias_schema'];
        }
        if(isset($this->contentTypeObject->data['is_container'])) {
            $contentTypeCreateStruct->isContainer = $this->contentTypeObject->data['is_container'];
        }
        if(isset($this->contentTypeObject->data['default_always_available'])) {
            $contentTypeCreateStruct->defaultAlwaysAvailable = $this->contentTypeObject->data['default_always_available'];
        }
        if(isset($this->contentTypeObject->data['default_sort_field'])) {
            $contentTypeCreateStruct->defaultSortField = $this->contentTypeObject->data['default_sort_field'];
        }
        if(isset($this->contentTypeObject->data['default_sort_order'])) {
            $contentTypeCreateStruct->defaultSortOrder = $this->contentTypeObject->data['default_sort_order'];
        }
    }

    /**
     * @param ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function fillContentTypeUpdateStruct(ContentTypeUpdateStruct $contentTypeUpdateStruct)
    {
        if(isset($this->contentTypeObject->data['names'])) {
            $contentTypeUpdateStruct->names = $this->contentTypeObject->data['names'];
        }
        if(isset($this->contentTypeObject->data['descriptions'])) {
            $contentTypeUpdateStruct->descriptions = $this->contentTypeObject->data['descriptions'];
        }
        if(isset($this->contentTypeObject->data['main_language_code'])) {
            $contentTypeUpdateStruct->mainLanguageCode = $this->contentTypeObject->data['main_language_code'];
        }
        if(isset($this->contentTypeObject->data['name_schema'])) {
            $contentTypeUpdateStruct->nameSchema = $this->contentTypeObject->data['name_schema'];
        }
        if(isset($this->contentTypeObject->data['url_alias_schema'])) {
            $contentTypeUpdateStruct->urlAliasSchema = $this->contentTypeObject->data['url_alias_schema'];
        }
        if(isset($this->contentTypeObject->data['is_container'])) {
            $contentTypeUpdateStruct->isContainer = $this->contentTypeObject->data['is_container'];
        }
        if(isset($this->contentTypeObject->data['default_always_available'])) {
            $contentTypeUpdateStruct->defaultAlwaysAvailable = $this->contentTypeObject->data['default_always_available'];
        }
        if(isset($this->contentTypeObject->data['default_sort_field'])) {
            $contentTypeUpdateStruct->defaultSortField = $this->contentTypeObject->data['default_sort_field'];
        }
        if(isset($this->contentTypeObject->data['default_sort_order'])) {
            $contentTypeUpdateStruct->defaultSortOrder = $this->contentTypeObject->data['default_sort_order'];
        }
    }
}
