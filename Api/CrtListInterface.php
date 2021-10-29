<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Api;

use GhostUnicorns\CrtBase\Exception\CrtException;

interface CrtListInterface
{
    /**
     * @return CollectorListProcessorInterface[]
     */
    public function getAllCollectorList(): array;

    /**
     * @return RefinerListProcessorInterface[]
     */
    public function getAllRefinerList(): array;

    /**
     * @return TransferorListProcessorInterface[]
     */
    public function getAllTransferorList(): array;

    /**
     * @param string $name
     * @return CollectorListProcessorInterface
     * @throws CrtException
     */
    public function getCollectorList(string $name): CollectorListProcessorInterface;

    /**
     * @param string $name
     * @return RefinerListProcessorInterface
     * @throws CrtException
     */
    public function getRefinerList(string $name): RefinerListProcessorInterface;

    /**
     * @param string $name
     * @return TransferorListProcessorInterface
     * @throws CrtException
     */
    public function getTransferorList(string $name): TransferorListProcessorInterface;
}
