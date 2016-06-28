<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager\Sub;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;

/**
 * Content type manager.
 *
 * @internal
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class ContentTypeGroupSubManager implements LoggerAwareInterface
{
    /**
     * @var ContentTypeService Content type service
     */
    private $contentTypeService;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * FieldDefinitionSubManager constructor.
     *
     * @param ContentTypeService $contentTypeService
     */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /*
     * @param array $identifiers
     *
     * @return ContentTypeGroup[]
     */
    public function loadContentTypeGroupsByIdentifiers(array $identifiers)
    {
        $contentTypeGroups = array_map(
            function ($identifier) {
                try {
                    return $this->contentTypeService->loadContentTypeGroupByIdentifier($identifier);
                } catch (NotFoundException $notFoundException) {
                    return $this->createContentTypeGroupByIdentifier($identifier);
                }
            },
            $identifiers
        );

        return $contentTypeGroups;
    }

    /**
     * @param string $contentTypeGroupIdentifier
     *
     * @return ContentTypeGroup
     */
    private function createContentTypeGroupByIdentifier($contentTypeGroupIdentifier)
    {
        $contentTypeGroupCreateStruct = new ContentTypeGroupCreateStruct();
        $contentTypeGroupCreateStruct->identifier = $contentTypeGroupIdentifier;

        return $this->contentTypeService->createContentTypeGroup($contentTypeGroupCreateStruct);
    }

    /**
     * @param ContentTypeObject $object
     *
     * @return bool
     *
     * @throws NotFoundException
     * @throws \Exception
     */
    public function updateContentTypeGroupsAssignment(ContentTypeObject $object)
    {
        // Load contenttype
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($object->data['identifier']);

        // Get identifiers of current contenttypegroups
        $currentContentTypeGroupIdentifiers = array_map(
            function (ContentTypeGroup $contentTypeGroup) {
                return $contentTypeGroup->identifier;
            },
            $contentType->getContentTypeGroups()
        );

        // Get new contenttypegroup identifiers
        $newContentTypeGroupIdentifiers = $object->data['contenttype_groups'];

        // Compare identifiers to identify which once to add/remove/keep
        $remove = array_diff($currentContentTypeGroupIdentifiers, $newContentTypeGroupIdentifiers);
        $add = array_diff($newContentTypeGroupIdentifiers, $currentContentTypeGroupIdentifiers);

        $this->attachContentTypeGroupsByIdentifiers($contentType, $add);
        $this->detachContentTypeGroupsByIdentifiers($contentType, $remove);

        return true;
    }

    /**
     * Load (and create if not exists) new contenttype groups, and assign them to a contenttype.
     *
     * @param ContentType $contentType
     * @param array       $contentTypeGroupsIdentifiers
     *
     * @throws NotFoundException
     */
    private function attachContentTypeGroupsByIdentifiers(ContentType $contentType, array $contentTypeGroupsIdentifiers)
    {
        $contentTypeGroups = $this->loadContentTypeGroupsByIdentifiers($contentTypeGroupsIdentifiers);
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $this->contentTypeService->assignContentTypeGroup($contentType, $contentTypeGroup);
        }
    }

    /**
     * Load contenttype groups, and unassign them from a contenttype.
     *
     * @param ContentType $contentType
     * @param array       $contentTypeGroupsIdentifiers
     *
     * @throws NotFoundException
     */
    private function detachContentTypeGroupsByIdentifiers(ContentType $contentType, array $contentTypeGroupsIdentifiers)
    {
        $contentTypeGroups = $this->loadContentTypeGroupsByIdentifiers($contentTypeGroupsIdentifiers);
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $this->contentTypeService->unassignContentTypeGroup($contentType, $contentTypeGroup);
        }
    }
}
