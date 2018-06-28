<?php

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Interface ModelInterface
 *
 * @package App\Models
 */
interface ModelInterface extends Arrayable
{

    const PROPERTY_ID = 'id';

    /**
     * @param int|null $id
     *
     * @return $this
     */
    public function setId(?int $id);

    /**
     * @return int|null
     */
    public function getId() : ?int;
}