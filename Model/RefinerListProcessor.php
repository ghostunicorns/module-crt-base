<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Model;

use Exception;
use GhostUnicorns\CrtActivity\Api\ActivityRepositoryInterface;
use GhostUnicorns\CrtActivity\Model\ActivityStateInterface;
use GhostUnicorns\CrtBase\Api\CrtConfigInterface;
use GhostUnicorns\CrtBase\Api\RefinerInterface;
use GhostUnicorns\CrtBase\Api\RefinerListProcessorInterface;
use GhostUnicorns\CrtBase\Exception\CrtException;
use GhostUnicorns\CrtBase\Exception\CrtImportantException;
use GhostUnicorns\CrtEntity\Api\Data\EntityInterface;
use GhostUnicorns\CrtEntity\Api\EntityRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Monolog\Logger;

class RefinerListProcessor implements RefinerListProcessorInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CrtConfigInterface
     */
    private $config;

    /**
     * @var EntityRepositoryInterface
     */
    private $entityRepository;

    /**
     * @var ActivityRepositoryInterface
     */
    private $activityRepository;

    /**
     * @var RefinerInterface[]
     */
    private $refiners;

    /**
     * @param Logger $logger
     * @param CrtConfigInterface $config
     * @param EntityRepositoryInterface $entityRepository
     * @param ActivityRepositoryInterface $activityRepository
     * @param array $refiners
     * @throws CrtException
     */
    public function __construct(
        Logger $logger,
        CrtConfigInterface $config,
        EntityRepositoryInterface $entityRepository,
        ActivityRepositoryInterface $activityRepository,
        array $refiners = []
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->entityRepository = $entityRepository;
        $this->activityRepository = $activityRepository;
        foreach ($refiners as $refiner) {
            if (!array_key_exists('model', $refiner) || !$refiner['model'] instanceof RefinerInterface) {
                throw new CrtException(__("Invalid type for refiner"));
            }
        }
        $this->refiners = $refiners;
    }

    /**
     * @param int $activityId
     * @throws NoSuchEntityException
     * @throws CrtImportantException
     * @throws Exception
     */
    public function execute(int $activityId): void
    {
        $activity = $this->activityRepository->getById($activityId);

        try {
            $allActivityEntities = $this->entityRepository->getAllByActivityIdGroupedByIdentifier($activityId);

            /** @var EntityInterface[] $entities */
            foreach ($allActivityEntities as $entityIdentifier => $entities) {
                $this->logger->info(__(
                    'activityId:%1 ~ RefinerList ~ entityIdentifier:%2 ~ START',
                    $activityId,
                    $entityIdentifier
                ));

                foreach ($this->refiners as $refinerType => $refiner) {
                    $this->logger->debug(__(
                        'activityId:%1 ~ Refiner ~ refinerType:%2 ~ entityIdentifier:%3 ~ START',
                        $activityId,
                        $refinerType,
                        $entityIdentifier
                    ));
                    try {
                        /** @var RefinerInterface $refinerModel */
                        $refinerModel = $refiner['model'];
                        $refinerModel->execute($activityId, $refinerType, (string)$entityIdentifier, $entities);
                    } catch (CrtImportantException $e) {
                        $this->logger->error(__(
                            'activityId:%1 ~ Refiner ~ refinerType:%2 ~ entityIdentifier:%3 ~ ERROR ~ error:%4',
                            $activityId,
                            $refinerType,
                            $entityIdentifier,
                            $e->getMessage()
                        ));
                        if (!$this->config->continueInCaseOfErrors()) {
                            throw $e;
                        }
                        break;
                    } catch (CrtException $e) {
                        $this->logger->notice(__(
                            'activityId:%1 ~ Refiner ~ refinerType:%2 ~ entityIdentifier:%3 ~ NOTICE ~ message:%4',
                            $activityId,
                            $refinerType,
                            $entityIdentifier,
                            $e->getMessage()
                        ));
                    }
                    $this->logger->debug(__(
                        'activityId:%1 ~ Refiner ~ refinerType:%2 ~ entityIdentifier:%3 ~ END',
                        $activityId,
                        $refinerType,
                        $entityIdentifier
                    ));

                    $activity->addExtraArray([
                        'refiner_' . $refinerType => ActivityStateInterface::REFINED
                    ]);
                    $this->activityRepository->save($activity);
                }

                foreach ($entities as $entity) {
                    $this->entityRepository->save($entity);
                }

                $this->logger->info(__(
                    'activityId:%1 ~ RefinerList ~ entityIdentifier:%2 ~ END',
                    $activityId,
                    $entityIdentifier
                ));
            }
        } catch (CrtImportantException $e) {
            if (isset($refinerType)) {
                $activity->addExtraArray([
                    'refiner_' . $refinerType => ActivityStateInterface::REFINE_ERROR
                ]);
                $this->activityRepository->save($activity);
            }
            $this->logger->error(__(
                'activityId:%1 ~ RefinerList ~ ERROR ~ error:%2',
                $activityId,
                $e->getMessage()
            ));
            throw $e;
        }

        $activity = $this->activityRepository->getById($activityId);
        $activity->setStatus(ActivityStateInterface::REFINED);
        $this->activityRepository->save($activity);
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return RefinerInterface[]
     */
    public function getRefiners(): array
    {
        return $this->refiners;
    }
}
