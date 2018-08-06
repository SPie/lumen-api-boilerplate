<?php

namespace App\Models\Auth;

use App\Models\AbstractDoctrineModel;
use App\Models\Timestamps;
use App\Models\User\UserModelInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LoginRefreshTokenDoctrineModel
 *
 * @ORM\Table(name="login_refresh_tokens")
 * @ORM\Entity(repositoryClass="App\Repositories\Auth\LoginRefreshTokenDoctrineRepository")
 *
 * @package App\Models\Auth
 */
class LoginRefreshTokenDoctrineModel extends AbstractDoctrineModel implements LoginRefreshTokenModelInterface
{

    use Timestamps;

    /**
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $token;

    /**
     * @ORM\Column(name="disabled_at", type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    private $disabledAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\User\UserDoctrineModel", inversedBy="refreshTokens", cascade={"persist"})
     *
     * @var UserModelInterface|null
     */
    private $user;

    /**
     * RefreshTokenDoctrineModel constructor.
     *
     * @param string                  $token
     * @param \DateTime|null          $disabledAt
     * @param UserModelInterface|null $user
     */
    public function __construct(string $token, ?\DateTime $disabledAt, ?UserModelInterface $user)
    {
        $this->token = $token;
        $this->disabledAt = $disabledAt;
        $this->user = $user;
    }

    /**
     * @param string $token
     *
     * @return LoginRefreshTokenModelInterface
     */
    public function setToken(string $token): LoginRefreshTokenModelInterface
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param \DateTime|null $disabledAt
     *
     * @return LoginRefreshTokenModelInterface
     */
    public function setDisabledAt(?\DateTime $disabledAt): LoginRefreshTokenModelInterface
    {
        $this->disabledAt = $disabledAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDisabledAt(): ?\DateTime
    {
        return $this->disabledAt;
    }

    /**
     * @param UserModelInterface|null $user
     *
     * @return LoginRefreshTokenModelInterface
     */
    public function setUser(?UserModelInterface $user): LoginRefreshTokenModelInterface
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return UserModelInterface|null
     */
    public function getUser(): ?UserModelInterface
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return \array_merge(
            parent::toArray(),
            [
                self::PROPERTY_TOKEN => $this->getToken(),
                self::PROPERTY_DISABLED_AT => $this->getDisabledAt()
                    ? (array)$this->getDisabledAt()
                    : null
            ]
        );
    }
}