<?php

namespace App\Models\User;

use App\Models\AbstractDoctrineModel;
use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Models\Authenticate;
use App\Models\SoftDelete;
use App\Models\Timestamps;
use App\Services\JWT\JWTObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Collection;
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
     * @ORM\OneToMany(targetEntity="App\Models\Auth\LoginRefreshTokenDoctrineModel", mappedBy="user", cascade={"persist"})
     *
     * @var LoginRefreshTokenModelInterface[]|ArrayCollection
     */
    private $loginRefreshTokens;

    /**
     * @var string|null
     */
    private $usedJwtRefreshToken;

    /**
     * UserDoctrineModel constructor.
     *
     * @param string                            $email
     * @param string                            $password
     * @param LoginRefreshTokenModelInterface[] $loginRefreshTokens
     */
    public function __construct(string $email, string $password, array $loginRefreshTokens = [])
    {
        $this->email = $email;
        $this->password = Hash::make($password);
        $this->loginRefreshTokens = new ArrayCollection($loginRefreshTokens);
        $this->usedJwtRefreshToken = null;
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
     * @param LoginRefreshTokenModelInterface[] $loginRefreshTokens
     *
     * @return UserModelInterface
     */
    public function setLoginRefreshTokens(array $loginRefreshTokens): UserModelInterface
    {
        $this->loginRefreshTokens = new ArrayCollection($loginRefreshTokens);

        return $this;
    }

    /**
     * @param LoginRefreshTokenModelInterface $loginRefreshToken
     *
     * @return UserModelInterface
     */
    public function addLoginRefreshToken(LoginRefreshTokenModelInterface $loginRefreshToken): UserModelInterface
    {
        if (!$this->loginRefreshTokens->contains($loginRefreshToken)) {
            $this->loginRefreshTokens->add($loginRefreshToken);
        }

        return $this;
    }

    /**
     * @return LoginRefreshTokenModelInterface[]|Collection
     */
    public function getLoginRefreshTokens(): Collection
    {
        return new Collection($this->loginRefreshTokens->toArray());
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
     * @param null|string $jwtRefreshToken
     *
     * @return $this
     */
    public function setUsedJWTRefreshToken(?string $jwtRefreshToken)
    {
        $this->usedJwtRefreshToken = $jwtRefreshToken;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsedJWTRefreshToken(): ?string
    {
        return $this->usedJwtRefreshToken;
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