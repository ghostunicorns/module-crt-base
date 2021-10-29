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
use Magento\Framework\Exception\NoSuchEntityException;

class RunAsync
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
     * @param CollectAction $collectAction
     * @param RefineAction $refineAction
     * @param TransferAction $transferAction
     */
    public function __construct(
        CollectAction $collectAction,
        RefineAction $refineAction,
        TransferAction $transferAction
    ) {
        $this->collectAction = $collectAction;
        $this->refineAction = $refineAction;
        $this->transferAction = $transferAction;
    }

    /**
     * @param string $type
     * @param string $extra
     * @throws CrtException
     * @throws NoSuchEntityException
     */
    public function execute(string $type, string $extra = '')
    {
        $activityId = $this->collectAction->execute($type, $extra);
        $this->refineAction->execute($type, $activityId);
        $this->transferAction->execute($type, $activityId);
    }
}
