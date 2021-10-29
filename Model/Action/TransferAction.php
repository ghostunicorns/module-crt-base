<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Model\Action;

use Exception;
use GhostUnicorns\CrtActivity\Api\ActivityRepositoryInterface;
use GhostUnicorns\CrtActivity\Model\ActivityModelFactory;
use GhostUnicorns\CrtActivity\Model\ActivityStateInterface;
use GhostUnicorns\CrtBase\Api\CrtListInterface;
use GhostUnicorns\CrtBase\Exception\CrtException;
use GhostUnicorns\CrtConfigDefinition\Model\ConfigInterface;

class TransferAction
{
    /**
     * @var CrtListInterface
     */
    private $crtList;

    /**
     * @var ActivityRepositoryInterface
     */
    private $activityRepository;

    /**
     * @var ActivityModelFactory
     */
    private $activityModelFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param CrtListInterface $crtList
     * @param ActivityRepositoryInterface $activityRepository
     * @param ActivityModelFactory $activityModelFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        CrtListInterface $crtList,
        ActivityRepositoryInterface $activityRepository,
        ActivityModelFactory $activityModelFactory,
        ConfigInterface $config
    ) {
        $this->crtList = $crtList;
        $this->activityRepository = $activityRepository;
        $this->activityModelFactory = $activityModelFactory;
        $this->config = $config;
    }

    /**
     * @param string $type
     * @param int|null $activityId
     * @return int
     */
    public function execute(string $type, int $activityId = null): ?int
    {
        $config = $this->config->getTypeConfig($type);
        if (!$config->isEnabled()) {
            throw new CrtException(__('The requested Crt type is disabled by configuration: %1', $type));
        }

        try {
            if ($activityId) {
                $activity = $this->activityRepository->getById($activityId);
            } else {
                $activity = $this->activityRepository->getFirstRefinedByType($type);
            }
            $activity->setStatus(ActivityStateInterface::TRANSFERING);
            $this->activityRepository->save($activity);

            $transferorList = $this->crtList->getTransferorList($type);
            $transferorList->execute((int)$activity->getId());
            return (int)$activity->getId();
        } catch (Exception $e) {
            if (isset($activity)) {
                $activity->setStatus(ActivityStateInterface::TRANSFER_ERROR);
                $activity->addExtraArray(['error' => $e->getMessage()]);
                $this->activityRepository->save($activity);
            }
        }
        return $activityId;
    }
}
