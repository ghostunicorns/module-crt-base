<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Model\Action;

use Exception;
use GhostUnicorns\CrtActivity\Api\ActivityRepositoryInterface;
use GhostUnicorns\CrtActivity\Model\ActivityModel;
use GhostUnicorns\CrtActivity\Model\ActivityModelFactory;
use GhostUnicorns\CrtActivity\Model\ActivityStateInterface;
use GhostUnicorns\CrtBase\Api\CrtListInterface;
use GhostUnicorns\CrtBase\Exception\CrtException;
use GhostUnicorns\CrtConfigDefinition\Model\ConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CollectAction
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
     * @param string $extra
     * @return int
     * @throws CrtException
     * @throws NoSuchEntityException
     */
    public function execute(string $type, string $extra = ''): int
    {
        $config = $this->config->getTypeConfig($type);
        if (!$config->isEnabled()) {
            throw new CrtException(__('The requested Crt type is disabled by configuration: %1', $type));
        }

        try {
            /** @var ActivityModel $activity */
            $activity = $this->activityModelFactory->create();
            $activity->setType($type);
            $activity->setStatus(ActivityStateInterface::COLLECTING);
            if ($extra !== '') {
                $activity->addExtraArray(['data' => $extra]);
            }
            $this->activityRepository->save($activity);

            $collectorList = $this->crtList->getCollectorList($type);
            $collectorList->execute((int)$activity->getId());
            return (int)$activity->getId();
        } catch (Exception $e) {
            if (isset($activity)) {
                $activity->setStatus(ActivityStateInterface::COLLECT_ERROR);
                $activity->addExtraArray(['error' => $e->getMessage()]);
                $this->activityRepository->save($activity);
            }
            throw $e;
        }
    }
}
