<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\LanguageObject;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;

/**
 * Content type manager.
 *
 * @internal
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class LanguageManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface Logger
     */
    private $logger;

    /**
     * @var LanguageService Language service
     */
    private $languageService;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->languageService = $repository->getContentLanguageService();
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Checks if language exists without throwing exceptions.
     *
     * @param string $code
     *
     * @return bool
     */
    public function exists($code)
    {
        try {
            $this->findByCode($code);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $code
     *
     * @return Language
     */
    public function findByCode($code)
    {
        return $this->languageService->loadLanguage($code);
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof LanguageObject) {
            return;
        }

        try {
            $language = $this->findByCode($object->data['code']);
            $this->languageService->enableLanguage($language);
        } catch (NotFoundException $notFoundException) {
            $languageCreateStruct = new LanguageCreateStruct();
            $languageCreateStruct->languageCode = $object->data['code'];
            $languageCreateStruct->name = $object->data['name'];
            $this->languageService->createLanguage($languageCreateStruct);
        }

        return new ValueObject($this->findByCode($object->data['code']));
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof LanguageObject) {
            return;
        }

        $language = $this->findByCode($object->data['code']);
        $this->languageService->updateLanguageName($language, $object->data['name']);

        return new ValueObject($this->findByCode($object->data['code']));
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof LanguageObject) {
            return;
        }
        if (!$this->exists($object->data['code'])) {
            return $this->create($object);
        } else {
            return $this->update($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof LanguageObject) {
            return;
        }

        try {
            $language = $this->findByCode($object->data['code']);
            $this->languageService->deleteLanguage($language);
        } catch (NotFoundException $notFoundException) {
            return true;
        } catch (InvalidArgumentException $notFoundException) {
            return false;
        }

        return true;
    }
}
