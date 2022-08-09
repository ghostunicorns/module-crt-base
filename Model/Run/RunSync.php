<?php
/*
 * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Model\Run;

use GhostUnicorns\CrtBase\Exception\CrtException;
use GhostUnicorns\CrtBase\Model\Action\CollectAction;
use GhostUnicorns\CrtBase\Model\Action\RefineAction;
use GhostUnicorns\CrtBase\Model\Action\TransferAction;
use GhostUnicorns\CrtEntity\Api\EntityRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class RunSync
{
    /**
     * @var CollectAction
     */
    private $collectAction;

    /**
     * @var RefineAction
     */
    private $refineAction;

    /**
     * @var TransferAction
     */
    private $transferAction;

    /**
     * @var EntityRepositoryInterface
     */
    private $entityRepository;

    /**
     * @param CollectAction $collectAction
     * @param RefineAction $refineAction
     * @param TransferAction $transferAction
     * @param EntityRepositoryInterface $entityRepository
     */
    public function __construct(
        CollectAction $collectAction,
        RefineAction $refineAction,
        TransferAction $transferAction,
        EntityRepositoryInterface $entityRepository
    ) {
        $this->collectAction = $collectAction;
        $this->refineAction = $refineAction;
        $this->transferAction = $transferAction;
        $this->entityRepository = $entityRepository;
    }

    /**
     * @param string $type
     * @param string $extra
     * @return array
     * @throws CrtException
     * @throws NoSuchEntityException
     */
    public function execute(string $type, string $extra = ''): array
    {
        $activityId = $this->collectAction->execute($type, $extra);
        $this->refineAction->execute($type, $activityId);
        $this->transferAction->execute($type, $activityId);
        return $this->entityRepository->getAllDataRefinedByActivityIdGroupedByIdentifier($activityId);
    }
}
