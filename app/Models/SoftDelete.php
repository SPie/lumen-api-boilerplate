<?php

namespace App\Models;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait SoftDelete
 *
 * @package App\Models
 */
trait SoftDelete
{

    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $deletedAt;

    /**
     * @param \DateTime|null $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(?\DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    /**
     * Restore the soft-deleted state
     *
     * @¶eturn $this
     */
    public function restore()
    {
        $this->deletedAt = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return ($this->deletedAt && (new \DateTime('now') >= $this->deletedAt));
    }
}