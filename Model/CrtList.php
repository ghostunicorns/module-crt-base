<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Model;

use GhostUnicorns\CrtBase\Api\CollectorListProcessorInterface;
use GhostUnicorns\CrtBase\Api\CrtListInterface;
use GhostUnicorns\CrtBase\Api\RefinerListProcessorInterface;
use GhostUnicorns\CrtBase\Api\TransferorListProcessorInterface;
use GhostUnicorns\CrtBase\Exception\CrtException;
use GhostUnicorns\CrtConfigDefinition\Model\ConfigInterface;

class CrtList implements CrtListInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CollectorListProcessorFactory
     */
    private $collectorListProcessorFactory;

    /**
     * @var RefinerListProcessorFactory
     */
    private $refinerListProcessorFactory;

    /**
     * @var TransferorListProcessorFactory
     */
    private $transferorListProcessorFactory;

    /**
     * @param ConfigInterface $config
     * @param CollectorListProcessorFactory $collectorListProcessorFactory
     * @param RefinerListProcessorFactory $refinerListProcessorFactory
     * @param TransferorListProcessorFactory $transferorListProcessorFactory
     */
    public function __construct(
        ConfigInterface $config,
        CollectorListProcessorFactory $collectorListProcessorFactory,
        RefinerListProcessorFactory $refinerListProcessorFactory,
        TransferorListProcessorFactory $transferorListProcessorFactory
    ) {
        $this->config = $config;
        $this->collectorListProcessorFactory = $collectorListProcessorFactory;
        $this->refinerListProcessorFactory = $refinerListProcessorFactory;
        $this->transferorListProcessorFactory = $transferorListProcessorFactory;
    }

    /**
     * @return CollectorListProcessorInterface[]
     */
    public function getAllCollectorList(): array
    {
        return $this->config->getAllCollectors();
    }

    /**
     * @return RefinerListProcessorInterface[]
     */
    public function getAllRefinerList(): array
    {
        return $this->config->getAllRefiners();
    }

    /**
     * @return TransferorListProcessorInterface[]
     */
    public function getAllTransferorList(): array
    {
        return $this->config->getAllTransferors();
    }

    /**
     * @param string $name
     * @return CollectorListProcessorInterface
     * @throws CrtException
     */
    public function getCollectorList(string $name): CollectorListProcessorInterface
    {
        $allCollectors = $this->config->getAllCollectors();
        if (!array_key_exists($name, $allCollectors)) {
            throw new CrtException(__('There is no a CollectorList with code: %1', $name));
        }

        $collectors = $allCollectors[$name];
        $logger = $this->config->getTypeLogger($name);

        return $this->collectorListProcessorFactory->create([
            'collectors' => $collectors,
            'logger' => $logger
        ]);
    }

    /**
     * @param string $name
     * @return RefinerListProcessorInterface
     * @throws CrtException
     */
    public function getRefinerList(string $name): RefinerListProcessorInterface
    {
        $allRefiners = $this->config->getAllRefiners();
        if (!array_key_exists($name, $allRefiners)) {
            throw new CrtException(__('There is not a RefinerList with code: %1', $name));
        }
        $refiners = $allRefiners[$name];
        $logger = $this->config->getTypeLogger($name);

        return $this->refinerListProcessorFactory->create([
            'refiners' => $refiners,
            'logger' => $logger
        ]);
    }

    /**
     * @param string $name
     * @return TransferorListProcessorInterface
     * @throws CrtException
     */
    public function getTransferorList(string $name): TransferorListProcessorInterface
    {
        $allTransferors = $this->config->getAllTransferors();
        if (!array_key_exists($name, $allTransferors)) {
            throw new CrtException(__('There is not a TransferorList with code: %1', $name));
        }
        $transferors = $allTransferors[$name];
        $logger = $this->config->getTypeLogger($name);

        return $this->transferorListProcessorFactory->create([
            'transferors' => $transferors,
            'logger' => $logger
        ]);
    }
}
