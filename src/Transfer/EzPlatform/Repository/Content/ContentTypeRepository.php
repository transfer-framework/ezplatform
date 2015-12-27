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
 * Contenttype repository.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class ContentTypeRepository
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
        $this->contentTypeObject = $contentTypeObject;
    }

    /**
     * @param ContentTypeCreateStruct $contentTypeCreateStruct
     */
    public function fillContentTypeCreateStruct(ContentTypeCreateStruct $contentTypeCreateStruct)
    {
        $contentTypeCreateStruct->names = $this->contentTypeObject->getNames();
        $contentTypeCreateStruct->descriptions = $this->contentTypeObject->getDescriptions();
        $contentTypeCreateStruct->remoteId = sha1(microtime());
        $contentTypeCreateStruct->mainLanguageCode = $this->contentTypeObject->mainLanguageCode;
        $contentTypeCreateStruct->nameSchema = $this->contentTypeObject->nameSchema;
        $contentTypeCreateStruct->urlAliasSchema = $this->contentTypeObject->urlAliasSchema;
        $contentTypeCreateStruct->isContainer = $this->contentTypeObject->isContainer;
        $contentTypeCreateStruct->defaultAlwaysAvailable = $this->contentTypeObject->defaultAlwaysAvailable;
        $contentTypeCreateStruct->defaultSortField = $this->contentTypeObject->defaultSortField;
        $contentTypeCreateStruct->defaultSortOrder = $this->contentTypeObject->defaultSortOrder;
    }

    /**
     * @param ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function fillContentTypeUpdateStruct(ContentTypeUpdateStruct $contentTypeUpdateStruct)
    {
        $contentTypeUpdateStruct->names = $this->contentTypeObject->getNames();
        $contentTypeUpdateStruct->descriptions = $this->contentTypeObject->getDescriptions();
        $contentTypeUpdateStruct->mainLanguageCode = $this->contentTypeObject->mainLanguageCode;
        $contentTypeUpdateStruct->nameSchema = $this->contentTypeObject->nameSchema;
        $contentTypeUpdateStruct->urlAliasSchema = $this->contentTypeObject->urlAliasSchema;
        $contentTypeUpdateStruct->isContainer = $this->contentTypeObject->isContainer;
        $contentTypeUpdateStruct->defaultAlwaysAvailable = $this->contentTypeObject->defaultAlwaysAvailable;
        $contentTypeUpdateStruct->defaultSortField = $this->contentTypeObject->defaultSortField;
        $contentTypeUpdateStruct->defaultSortOrder = $this->contentTypeObject->defaultSortOrder;
    }
}
