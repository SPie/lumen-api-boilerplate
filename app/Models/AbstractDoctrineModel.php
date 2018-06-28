<?php

namespace App\Models;

use Doctrine\ORM\Mapping;

/**
 * Class AbstractDoctrineModel
 *
 * @package App\Models
 */
class AbstractDoctrineModel implements ModelInterface
{

    /**
     * @Mapping\Id
     * @Mapping\GeneratedValue
     * @Mapping\Column(type="integer")
     *
     * @var int
     */
    public $id;

    /**
     * @param int|null $id
     *
     * @return $this
     */
    public function setId(?int $id) {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId() : ?int {
        return $this->id;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::PROPERTY_ID => $this->getId(),
        ];
    }
}