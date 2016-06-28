<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Adapter;

use eZ\Publish\API\Repository\Repository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Transfer\Adapter\TargetAdapterInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\Adapter\Transaction\Response;
use Transfer\Data\ObjectInterface;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\Repository\Manager\Core\AbstractRepositoryService;
use Transfer\EzPlatform\Repository\Manager\Core\ContentTreeService;
use Transfer\EzPlatform\Repository\Manager\Core\ObjectService;
use Transfer\EzPlatform\Repository\Values\EzPlatformObject;

/**
 * eZ Platform adapter.
 */
class EzPlatformAdapter implements TargetAdapterInterface, LoggerAwareInterface
{
    /**
     * @var array Options
     */
    protected $options;

    /**
     * @var LoggerInterface Logger
     */
    protected $logger;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var ContentTreeService Tree service
     */
    protected $treeService;

    /**
     * @var ObjectService Object service
     */
    protected $objectService;

    /**
     * Constructor.
     *
     * @param Repository $repository
     * @param array      $options
     */
    public function __construct(Repository $repository, array $options = array())
    {
        $this->repository = $repository;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);

        $this->objectService = new ObjectService($repository, $this->options);
        $this->treeService = new ContentTreeService($repository, $this->options, $this->objectService);
    }

    /**
     * Option configuration.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'current_user' => 'admin',
            'main_language_code' => 'eng-GB',
        ));

        $resolver->setAllowedTypes('current_user', array('string', 'null'));
        $resolver->setAllowedTypes('main_language_code', array('string', 'null'));
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->objectService->setLogger($logger);
        $this->treeService->setLogger($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function send(Request $request)
    {
        $this->repository->beginTransaction();

        if ($this->logger) {
            $this->treeService->setLogger($this->logger);
            $this->objectService->setLogger($this->logger);
        }

        $response = new Response();

        $objects = array();
        foreach ($request as $object) {
            $service = $this->getService($object);

            try {
                $objects[] = $this->executeAction($object, $service);
            } catch (\Exception $e) {
                $this->repository->rollback();
                throw $e;
            }

            if (!empty($objects)) {
                $response->setData(new \ArrayIterator($objects));
            }
        }

        $this->repository->commit();

        return $response;
    }

    /**
     * @param ObjectInterface           $object
     * @param AbstractRepositoryService $service
     *
     * @return ObjectInterface|null
     */
    protected function executeAction(ObjectInterface $object, AbstractRepositoryService $service)
    {
        if (is_a($object, EzPlatformObject::class)) {
            /** @var EzPlatformObject $object */
            switch ($object->getAction()) {
                case Action::CREATEORUPDATE:
                    return $service->createOrUpdate($object);
                case Action::DELETE:
                    return $service->remove($object);
                case Action::SKIP:
                default:
            }
        } else {
            return $service->createOrUpdate($object);
        }

        return;
    }

    /**
     * Decides which service to use, based on the type of $object given.
     *
     * @param ObjectInterface $object
     *
     * @return ContentTreeService|ObjectService
     */
    protected function getService($object)
    {
        if ($object instanceof TreeObject) {
            $service = $this->treeService;
        } else {
            $service = $this->objectService;
        }

        if ($this->options['current_user']) {
            $service->setCurrentUser($this->options['current_user']);
        }

        return $service;
    }
}
