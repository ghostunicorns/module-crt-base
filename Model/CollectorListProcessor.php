<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Model;

use GhostUnicorns\CrtActivity\Api\ActivityRepositoryInterface;
use GhostUnicorns\CrtActivity\Model\ActivityStateInterface;
use GhostUnicorns\CrtBase\Api\CollectorInterface;
use GhostUnicorns\CrtBase\Api\CollectorListProcessorInterface;
use GhostUnicorns\CrtBase\Exception\CrtException;
use Magento\Framework\Exception\NoSuchEntityException;
use Monolog\Logger;

class CollectorListProcessor implements CollectorListProcessorInterface
{
    /**
     * @var ActivityRepositoryInterface
     */
    private $activityRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CollectorInterface[]
     */
    private $collectors;

    /**
     * @param ActivityRepositoryInterface $activityRepository
     * @param Logger $logger
     * @param array $collectors
     * @throws CrtException
     */
    public function __construct(
        ActivityRepositoryInterface $activityRepository,
        Logger $logger,
        array $collectors = []
    ) {
        $this->activityRepository = $activityRepository;
        $this->logger = $logger;
        foreach ($collectors as $collector) {
            if (!array_key_exists('model', $collector) || !$collector['model'] instanceof CollectorInterface) {
                throw new CrtException(__("Invalid type for collector"));
            }
        }
        $this->collectors = $collectors;
    }

    /**
     * @param int $activityId
     * @throws CrtException
     * @throws NoSuchEntityException
     */
    public function execute(int $activityId): void
    {
        $activity = $this->activityRepository->getById($activityId);

        try {
            $this->logger->info(__(
                'activityId:%1 ~ CollectorListProcessor ~ type:%2 ~ START',
                $activityId,
                $activity->getType()
            ));
            foreach ($this->collectors as $collectorType => $collector) {
                $activity->addExtraArray(['collector_' . $collectorType => ActivityStateInterface::COLLECTING]);
                $this->activityRepository->save($activity);

                $this->logger->debug(__(
                    'activityId:%1 ~ Collector ~ collectorType:%2 ~ START',
                    $activityId,
                    $collectorType
                ));
                /** @var CollectorInterface $collectorModel */
                $collectorModel = $collector['model'];
                $collectorModel->execute($activityId, $collectorType);
                $this->logger->debug(__(
                    'activityId:%1 ~ Collector ~ collectorType:%2 ~ END',
                    $activityId,
                    $collectorType
                ));

                $activity = $this->activityRepository->getById($activityId);
                $activity->addExtraArray(['collector_' . $collectorType => ActivityStateInterface::COLLECTED]);
                $this->activityRepository->save($activity);
            }
            $this->logger->info(__(
                'activityId:%1 ~ CollectorListProcessor ~ type:%2 ~ END',
                $activityId,
                $activity->getType()
            ));
        } catch (CrtException $e) {
            if (isset($collectorType)) {
                $activity->addExtraArray(['collector_' . $collectorType => ActivityStateInterface::COLLECT_ERROR]);
                $this->activityRepository->save($activity);
            }
            $this->logger->error(__(
                'activityId:%1 ~ CollectorListProcessor ~ type:%2 ~ ERROR ~ error:%3',
                $activityId,
                $activity->getType(),
                $e->getMessage()
            ));
            throw $e;
        }

        $activity = $this->activityRepository->getById($activityId);
        $activity->setStatus(ActivityStateInterface::COLLECTED);
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
     * @return CollectorInterface[]
     */
    public function getCollectors(): array
    {
        return $this->collectors;
    }
}
