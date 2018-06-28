<?php

namespace App\Models;

/**
 * Interface SoftDeletable
 *
 * @package App\Models
 */
interface SoftDeletable
{

    const PROPERTY_DELETED_AT = 'deletedAt';

    /**
     * @param \DateTime $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(?\DateTime $deletedAt);

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt(): ?\DateTime;

    /**
     * @return $this
     */
    public function restore();

    /**
     * @return bool
     */
    public function isDeleted(): bool;
}