<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Model;

use GhostUnicorns\CrtActivity\Api\ActivityRepositoryInterface;
use GhostUnicorns\CrtActivity\Model\ActivityStateInterface;
use GhostUnicorns\CrtBase\Api\TransferorInterface;
use GhostUnicorns\CrtBase\Api\TransferorListProcessorInterface;
use GhostUnicorns\CrtBase\Exception\CrtException;
use Magento\Framework\Exception\NoSuchEntityException;
use Monolog\Logger;

class TransferorListProcessor implements TransferorListProcessorInterface
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
     * @var TransferorInterface[]
     */
    private $transferors;

    /**
     * @param ActivityRepositoryInterface $activityRepository
     * @param Logger $logger
     * @param array $transferors
     * @throws CrtException
     */
    public function __construct(
        ActivityRepositoryInterface $activityRepository,
        Logger $logger,
        array $transferors = []
    ) {
        $this->logger = $logger;
        $this->activityRepository = $activityRepository;
        foreach ($transferors as $transferor) {
            if (!array_key_exists('model', $transferor) || !$transferor['model'] instanceof TransferorInterface) {
                throw new CrtException(__("Invalid type for transferor"));
            }
        }
        $this->transferors = $transferors;
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
                'activityId:%1 ~ TransferorListProcessor ~ type:%2 ~ START',
                $activityId,
                $activity->getType()
            ));
            foreach ($this->transferors as $transferorType => $transferor) {
                $activity->addExtraArray(['transferor_' . $transferorType => ActivityStateInterface::TRANSFERING]);
                $this->activityRepository->save($activity);

                $this->logger->debug(__(
                    'activityId:%1 ~ Transferor ~ transferorType:%2 ~ START',
                    $activityId,
                    $transferorType
                ));
                /** @var TransferorInterface $transferorModel */
                $transferorModel = $transferor['model'];
                $transferorModel->execute($activityId, $transferorType);
                $this->logger->debug(__(
                    'activityId:%1 ~ Transferor ~ transferorType:%2 ~ END',
                    $activityId,
                    $transferorType
                ));

                $activity = $this->activityRepository->getById($activityId);
                $activity->addExtraArray(['transferor_' . $transferorType => ActivityStateInterface::TRANSFERED]);
                $this->activityRepository->save($activity);
            }
            $this->logger->info(__(
                'activityId:%1 ~ TransferorListProcessor ~ type:%2 ~ END',
                $activityId,
                $activity->getType()
            ));
        } catch (CrtException $e) {
            if (isset($transferorType)) {
                $activity->addExtraArray(['transferor_' . $transferorType => ActivityStateInterface::TRANSFER_ERROR]);
                $this->activityRepository->save($activity);
            }
            $this->logger->error(__(
                'activityId:%1 ~ TransferorListProcessor ~ type:%2 ~ ERROR ~ error:%3',
                $activityId,
                $activity->getType(),
                $e->getMessage()
            ));
            throw $e;
        }

        $activity = $this->activityRepository->getById($activityId);
        $activity->setStatus(ActivityStateInterface::TRANSFERED);
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
     * @return TransferorInterface[]
     */
    public function getTransferors(): array
    {
        return $this->transferors;
    }
}
