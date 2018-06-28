<?php

namespace App\Models\User;

use App\Models\AbstractDoctrineModel;
use App\Models\Authenticate;
use App\Models\SoftDelete;
use App\Models\Timestamps;
use App\Services\JWT\JWTObject;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserDoctrineModel
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repositories\User\UserDoctrineRepository")
 *
 * @package App\Models\User
 */
class UserDoctrineModel extends AbstractDoctrineModel implements UserModelInterface
{

    use Authenticate;
    use Timestamps;
    use SoftDelete;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $email;

    /**
     * @var JWTObject|null
     */
    private $jwtObject;

    /**
     * UserDoctrineModel constructor.
     *
     * @param string $email
     * @param string $password
     */
    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = Hash::make($password);
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return int
     */
    public function getJWTIdentifier(): int
    {
        return $this->getId();
    }

    /**
     * @return array
     */
    public function getCustomClaims(): array
    {
        return [
            self::PROPERTY_EMAIL => $this->getEmail(),
        ];
    }

    /**
     * @param JWTObject|null $jwtObject
     *
     * @return $this
     */
    public function setJWTObject(?JWTObject $jwtObject)
    {
        $this->jwtObject = $jwtObject;

        return $this;
    }

    /**
     * @return JWTObject|null
     */
    public function getJWTObject(): ?JWTObject
    {
        return $this->jwtObject;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return \array_merge(
            parent::toArray(),
            [
                self::PROPERTY_EMAIL      => $this->getEmail(),
                self::PROPERTY_CREATED_AT => $this->getCreatedAt()
                    ? (array)$this->getCreatedAt()
                    : null,
                self::PROPERTY_UPDATED_AT => $this->getUpdatedAt()
                    ? (array)$this->getUpdatedAt()
                    : null,
                self::PROPERTY_DELETED_AT => $this->getDeletedAt()
                    ? (array)$this->getDeletedAt()
                    : null,
            ]
        );
    }
}