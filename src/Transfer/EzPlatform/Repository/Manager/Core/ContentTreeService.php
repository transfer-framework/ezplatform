<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager\Core;

use eZ\Publish\API\Repository\Repository;
use Transfer\Data\ObjectInterface;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\Repository\Values\EzPlatformObject;

/**
 * Content tree service.
 *
 * @internal
 */
class ContentTreeService extends AbstractRepositoryService
{
    /**
     * @var ObjectService Object service
     */
    protected $objectService;

    /**
     * @param Repository    $repository
     * @param array         $options
     * @param ObjectService $objectService Object service
     */
    public function __construct(Repository $repository, array $options, ObjectService $objectService)
    {
        parent::__construct($repository, $options);
        $this->objectService = $objectService;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate($object)
    {
        if (!$object instanceof TreeObject) {
            throw new UnsupportedObjectOperationException(TreeObject::class, get_class($object));
        }

        $this->publishContentObjects($object);
    }

    /**
     * Publishes content objects.
     *
     * @param TreeObject $object
     *
     * @throws \InvalidArgumentException
     */
    private function publishContentObjects(TreeObject $object)
    {
        $last = $this->objectService->createOrUpdate($object->data);

        foreach ($object->getNodes() as $subObject) {
            if ($subObject instanceof TreeObject) {
                $this->publishContentObjects($subObject);
            } else {
                /* @var ContentObject $subObject */
                $subObject->addParentLocation($last->getProperty('content_info')->mainLocationId);
                $this->objectService->createOrUpdate($subObject);
            }
        }
    }

    /**
     * Bulk-deletions is not supported.
     *
     * @param ObjectInterface $object
     */
    public function remove($object)
    {
        if ($this->logger) {
            $this->logger->warning(sprintf(
                'Attempted to delete using %s, which is not supported. Use the implementations of %s instead.',
                __CLASS__,
                EzPlatformObject::class
            ));
        }

        return;
    }
}
