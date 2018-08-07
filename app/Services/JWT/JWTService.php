<?php

namespace App\Services\JWT;

use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelInterface;
use App\Services\Auth\LoginRefreshTokenServiceInterface;
use App\Services\User\UsersServiceInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

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
    const PAYLOAD_PARAMETER_REFRESH    = 'refresh';

    /**
     * @var LoginRefreshTokenServiceInterface
     */
    private $loginRefreshTokenService;

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
     * @param LoginRefreshTokenServiceInterface $loginRefreshTokenService
     * @param string                            $issuer
     * @param string                            $secret
     * @param int                               $expiryHours
     */
    public function __construct(
        LoginRefreshTokenServiceInterface $loginRefreshTokenService,
        string $issuer,
        string $secret,
        int $expiryHours = 1
    )
    {
        $this->loginRefreshTokenService = $loginRefreshTokenService;
        $this->issuer = $issuer;
        $this->secret = $secret;
        $this->expiryHours = $expiryHours;
    }

    /**
     * @return LoginRefreshTokenServiceInterface
     */
    protected function getLoginRefreshTokenService(): LoginRefreshTokenServiceInterface
    {
        return $this->loginRefreshTokenService;
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
     * @param bool               $withRefreshToken
     *
     * @return JWTObject
     *
     * @throws \Exception
     */
    public function createToken(JWTAuthenticatable $user, bool $withRefreshToken = true): JWTObject
    {
        return $this->createJwtObject(
            $user,
            $withRefreshToken
                ? $this->createRefreshJwt($user)
                : null
        );
    }

    /**
     * @param JWTAuthenticatable $user
     *
     * @return JWTObject
     *
     * @throws \Exception
     */
    public function refreshAuthToken(JWTAuthenticatable $user): JWTObject
    {
        return $this->createJwtObject($user, $user->getUsedJWTRefreshToken());
    }

    /**
     * @param string                            $token
     * @param UsersServiceInterface             $usersService
     *
     * @return JWTAuthenticatable|null
     */
    public function getAuthenticatedUser(string $token, UsersServiceInterface $usersService): ?JWTAuthenticatable
    {
        try {
            $claims = JWT::decode($token, $this->getSecret(), $this->getAllowedAlgos());

            if (empty($claims->{self::PAYLOAD_PARAMETER_SUBJECT})) {
                return null;
            }

            try {
                return $usersService->getUser($claims->{self::PAYLOAD_PARAMETER_SUBJECT})
                    ->setUsedJWTRefreshToken($this->getRefreshJwt($token));
            } catch (ModelNotFoundException $e) {
                return null;
            }
        } catch (ExpiredException $e) {
            $refreshJwt = $this->getRefreshJwt($token);

            if (!$refreshJwt) {
                return null;
            }

            $user = $this->getLoginRefreshTokenService()->useLoginRefreshToken($refreshJwt);
            if (!$user) {
                return null;
            }

            return $user->setUsedJWTRefreshToken($this->createRefreshJwt($user));
        } catch (\UnexpectedValueException $e) {
            return null;
        }
    }

    /**
     * @param JWTAuthenticatable $user
     *
     * @return JWTAuthenticatable
     */
    public function deauthenticate(JWTAuthenticatable $user): JWTAuthenticatable
    {
        if ($user->getUsedJWTRefreshToken()) {
            $this->getLoginRefreshTokenService()->disableRefreshToken($user->getUsedJWTRefreshToken());
        }

        return $user->setUsedJWTRefreshToken(null);
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
     * @param string|null        $refreshJwt
     *
     * @return JWTObject
     * @throws \Exception
     */
    protected function createJwtObject(JWTAuthenticatable $user, string $refreshJwt = null): JWTObject
    {
        list($issuedAt, $expiresAt) = $this->createTimestamps();

        return new JWTObject(
            $this->createJwt(
                $user,
                $issuedAt,
                $expiresAt,
                $refreshJwt
            ),
            $expiresAt,
            $refreshJwt
        );
    }

    /**
     * @param JWTAuthenticatable $user
     * @param \DateTime          $issuedAt
     * @param \DateTime          $expiresAt
     * @param string|null        $refreshToken
     *
     * @return string
     */
    protected function createJwt(
        JWTAuthenticatable $user,
        \DateTime $issuedAt,
        \DateTime $expiresAt,
        string $refreshToken = null
    ): string
    {
        return JWT::encode(
            \array_merge(
                [
                    self::PAYLOAD_PARAMETER_ISSUER     => $this->getIssuer(),
                    self::PAYLOAD_PARAMETER_ISSUED_AT  => $issuedAt->getTimestamp(),
                    self::PAYLOAD_PARAMETER_EXPIRES_AT => $expiresAt->getTimestamp(),
                    self::PAYLOAD_PARAMETER_SUBJECT    => $user->getJWTIdentifier(),
                    self::PAYLOAD_PARAMETER_RANDOM     => \md5(\mt_rand() . \time()),
                    self::PAYLOAD_PARAMETER_REFRESH    => $refreshToken,
                ],
                $user->getCustomClaims()
            ),
            $this->getSecret()
        );
    }

    /**
     * @param JWTAuthenticatable|UserModelInterface $user
     *
     * @return string
     */
    protected function createRefreshJwt(JWTAuthenticatable $user): string
    {
        return $this->getLoginRefreshTokenService()->createLoginRefreshToken($user)->getToken();
    }

    /**
     * @param string $token
     *
     * @return string|null
     */
    protected function getRefreshJwt(string $token): ?string
    {
        $tks = explode('.', $token);
        if (count($tks) != 3) {
            return null;
        }
        list($headb64, $bodyb64) = $tks;
        if (null === ($header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64)))) {
            return null;
        }
        if (null === $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64))) {
            return null;
        }

        return $payload->{self::PAYLOAD_PARAMETER_REFRESH} ?? null;
    }
}