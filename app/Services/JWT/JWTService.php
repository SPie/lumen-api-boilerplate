<?php

namespace App\Services\JWT;

use App\Exceptions\ModelNotFoundException;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\UsersServiceInterface;
use Firebase\JWT\JWT;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;

/**
 * Class JWTService
 *
 * @package App\Services\JWT
 */
class JWTService implements JWTServiceInterface
{

    const PAYLOAD_PARAMETER_ISSUER     = 'iss';
    const PAYLOAD_PARAMETER_ISSUED_AT  = 'iat';
    const PAYLOAD_PARAMETER_EXPIRES_AT = 'exp';
    const PAYLOAD_PARAMETER_SUBJECT    = 'sub';
    const PAYLOAD_PARAMETER_RANDOM     = 'rnd';

    /**
     * @var string
     */
    private $issuer;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var int
     */
    private $expiryHours;

    /**
     * @var array
     */
    private $allowedAlgos = [
        'HS256',
    ];

    /**
     * JWTService constructor.
     *
     * @param string                  $issuer
     * @param string                  $secret
     * @param int                     $expiryHours
     */
    public function __construct(
        string $issuer,
        string $secret,
        int $expiryHours = 1
    )
    {
        $this->issuer = $issuer;
        $this->secret = $secret;
        $this->expiryHours = $expiryHours;
    }

    /**
     * @return string
     */
    protected function getIssuer(): string
    {
        return $this->issuer;
    }

    /**
     * @return string
     */
    protected function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return int
     */
    protected function getExpiryHours(): int
    {
        return $this->expiryHours;
    }

    /**
     * @return array
     */
    protected function getAllowedAlgos(): array
    {
        return $this->allowedAlgos;
    }

    /**
     * @param JWTAuthenticatable $user
     *
     * @return JWTObject
     *
     * @throws \Exception
     */
    public function createToken(JWTAuthenticatable $user): JWTObject
    {
        list($issuedAt, $expiresAt) = $this->createTimestamps();

        return new JWTObject($this->createJwt($user, $issuedAt, $expiresAt), $expiresAt);
    }

    /**
     * @param JWTAuthenticatable $user
     *
     * @return JWTObject
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function refreshAuthToken(JWTAuthenticatable $user): JWTObject
    {
        if (!$user->getJWTObject()) {
            throw new AuthorizationException();
        }

        //store blacklisted token in cache
        Cache::add(
            \md5($user->getJWTObject()->getToken()),
            $user->getJWTObject()->getToken(),
            $this->createBlacklistExpiry($user->getJWTObject()->getExpiresAt())
        );

        return $this->createToken($user);
    }

    /**
     * @param string                $token
     * @param UsersServiceInterface $usersService
     *
     * @return JWTAuthenticatable|null
     */
    public function getAuthenticatedUser(string $token, UsersServiceInterface $usersService): ?JWTAuthenticatable
    {
        if (!empty(Cache::get(\md5($token)))) {
            return null;
        }

        try {
            $claims = JWT::decode($token, $this->getSecret(), $this->getAllowedAlgos());
        } catch (\UnexpectedValueException $e) {
            return null;
        }

        if (empty($claims->{self::PAYLOAD_PARAMETER_SUBJECT})) {
            return null;
        }

        try {
            return $usersService->getUser($claims->{self::PAYLOAD_PARAMETER_SUBJECT})
                ->setJWTObject(new JWTObject(
                    $token,
                    (new \DateTime())->setTimestamp($claims->{self::PAYLOAD_PARAMETER_EXPIRES_AT})
                ));
        } catch (ModelNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param JWTAuthenticatable $user
     *
     * @return JWTAuthenticatable
     *
     * @throws AuthorizationException
     */
    public function deauthenticate(JWTAuthenticatable $user): JWTAuthenticatable
    {
        if (!$user->getJWTObject()) {
            throw new AuthorizationException();
        }

        Cache::add(
            \md5($user->getJWTObject()->getToken()),
            $user->getJWTObject()->getToken(),
            $this->createBlacklistExpiry($user->getJWTObject()->getExpiresAt())
        );

        return $user->setJWTObject(null);
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function createTimestamps(): array
    {
        $issuedAt = new \DateTime();

        return [
            $issuedAt,
            (clone $issuedAt)->add(new \DateInterval('PT' . $this->getExpiryHours() . 'H')),
        ];
    }

    /**
     * @param JWTAuthenticatable $user
     * @param \DateTime          $issuedAt
     * @param \DateTime          $expiresAt
     *
     * @return string
     */
    protected function createJwt(JWTAuthenticatable $user, \DateTime $issuedAt, \DateTime $expiresAt): string
    {
        return JWT::encode(
            \array_merge(
                [
                    self::PAYLOAD_PARAMETER_ISSUER     => $this->getIssuer(),
                    self::PAYLOAD_PARAMETER_ISSUED_AT  => $issuedAt->getTimestamp(),
                    self::PAYLOAD_PARAMETER_EXPIRES_AT => $expiresAt->getTimestamp(),
                    self::PAYLOAD_PARAMETER_SUBJECT    => $user->getJWTIdentifier(),
                    self::PAYLOAD_PARAMETER_RANDOM     => \md5(\mt_rand() . \time()),
                ],
                $user->getCustomClaims()
            ),
            $this->getSecret()
        );
    }

    /**
     * @param \DateTime $expiresAt
     *
     * @return \DateInterval
     */
    protected function createBlacklistExpiry(\DateTime $expiresAt): \DateInterval
    {
        return ($expiresAt && $expiresAt > new \DateTime())
            ? (new \DateTime())->diff($expiresAt)
            : new \DateInterval('PT0M');
    }
}