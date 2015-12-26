<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Data\LanguageObject;

/**
 * Content type manager.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class LanguageManager implements LoggerAwareInterface
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
     * Enables language if it exists, else creates it.
     *
     * @param LanguageObject $object
     *
     * @return Language
     */
    public function add(LanguageObject $object)
    {
        try {
            $language = $this->languageService->loadLanguage($object->code);
            $this->languageService->enableLanguage($language);
        } catch (NotFoundException $notFoundException) {
            $languageCreateStruct = new LanguageCreateStruct();
            $languageCreateStruct->languageCode = $object->code;
            $languageCreateStruct->name = $object->getName();
            $this->languageService->createLanguage($languageCreateStruct);
        }

        return $this->languageService->loadLanguage($object->code);
    }
}
