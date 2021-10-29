<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Model\Action;

use Exception;
use GhostUnicorns\CrtActivity\Api\ActivityRepositoryInterface;
use GhostUnicorns\CrtActivity\Model\ActivityStateInterface;
use GhostUnicorns\CrtBase\Api\CrtListInterface;
use GhostUnicorns\CrtBase\Exception\CrtException;
use GhostUnicorns\CrtConfigDefinition\Model\ConfigInterface;
use GhostUnicorns\CrtEntity\Model\ClearDataRefinedByActivityId;

class RefineAction
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
     * @var ClearDataRefinedByActivityId
     */
    private $clearDataRefinedByActivityId;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param CrtListInterface $crtList
     * @param ActivityRepositoryInterface $activityRepository
     * @param ClearDataRefinedByActivityId $clearDataRefinedByActivityId
     * @param ConfigInterface $config
     */
    public function __construct(
        CrtListInterface $crtList,
        ActivityRepositoryInterface $activityRepository,
        ClearDataRefinedByActivityId $clearDataRefinedByActivityId,
        ConfigInterface $config
    ) {
        $this->crtList = $crtList;
        $this->activityRepository = $activityRepository;
        $this->clearDataRefinedByActivityId = $clearDataRefinedByActivityId;
        $this->config = $config;
    }

    /**
     * @param string $type
     * @param int|null $activityId
     * @return int
     * @throws CrtException
     */
    public function execute(string $type, int $activityId = null): ?int
    {
        $config = $this->config->getTypeConfig($type);
        if (!$config->isEnabled()) {
            throw new CrtException(__('The requested Crt type is disabled by configuration: %1', $type));
        }

        try {
            if ($activityId) {
                $this->clearDataRefinedByActivityId->execute($activityId);
                $activity = $this->activityRepository->getById($activityId);
            } else {
                $activity = $this->activityRepository->getFirstCollectedByType($type);
            }
            $activity->setStatus(ActivityStateInterface::REFINING);
            $this->activityRepository->save($activity);

            $refinerList = $this->crtList->getRefinerList($type);
            $refinerList->execute((int)$activity->getId());
            return (int)$activity->getId();
        } catch (Exception $e) {
            if (isset($activity)) {
                $activity->setStatus(ActivityStateInterface::REFINE_ERROR);
                $activity->addExtraArray(['error' => $e->getMessage()]);
                $this->activityRepository->save($activity);
            }
        }
        return $activityId;
    }
}
