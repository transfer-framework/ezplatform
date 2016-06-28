<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\ObjectNotFoundException;
use Transfer\EzPlatform\Repository\Values\LanguageObject;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\FinderInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;

/**
 * Content type manager.
 *
 * @internal
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class LanguageManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface, FinderInterface
{
    /**
     * @var LanguageService Language service
     */
    private $languageService;

    /**
     * @var LoggerInterface Logger
     */
    private $logger;

    /**
     * @param LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function find(ValueObject $object)
    {
        try {
            if (isset($object->data['code'])) {
                $language = $this->languageService->loadLanguage($object->data['code']);
            }
        } catch (NotFoundException $notFoundException) {
            // We'll throw our own exception later instead.
        }

        if (!isset($language)) {
            throw new ObjectNotFoundException(Language::class, array('code'));
        }

        return $language;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof LanguageObject) {
            throw new UnsupportedObjectOperationException(LanguageObject::class, get_class($object));
        }

        try {
            $language = $this->find($object);
            $this->languageService->enableLanguage($language);
        } catch (NotFoundException $notFoundException) {
            $languageCreateStruct = $this->languageService->newLanguageCreateStruct();
            $object->getMapper()->mapObjectToCreateStruct($languageCreateStruct);
            $language = $this->languageService->createLanguage($languageCreateStruct);
        }

        $object->getMapper()->languageToObject($language);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof LanguageObject) {
            throw new UnsupportedObjectOperationException(LanguageObject::class, get_class($object));
        }

        $language = $this->find($object);
        $language = $this->languageService->updateLanguageName($language, $object->data['name']);

        $object->getMapper()->languageToObject($language);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof LanguageObject) {
            throw new UnsupportedObjectOperationException(LanguageObject::class, get_class($object));
        }

        try {
            $this->find($object);

            return $this->update($object);
        } catch (ObjectNotFoundException $notFound) {
            return $this->create($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof LanguageObject) {
            throw new UnsupportedObjectOperationException(LanguageObject::class, get_class($object));
        }

        try {
            $language = $this->find($object);
            $this->languageService->deleteLanguage($language);
        } catch (NotFoundException $e) {
            return true;
        } catch (InvalidArgumentException $ee) {
            if ($this->logger) {
                $this->logger->warning('Tried to delete the main language, or a language that still has existing translations (is in use).');
            }

            return false;
        }

        return true;
    }
}
