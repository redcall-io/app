<?php

namespace Bundles\ApiBundle\Manager;

use Bundles\ApiBundle\Entity\Token;
use Bundles\ApiBundle\Repository\TokenRepository;
use Bundles\ApiBundle\Util;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Security;

class TokenManager
{
    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    /**
     * @var Security
     */
    private $security;

    public function __construct(TokenRepository $tokenRepository, Security $security)
    {
        $this->tokenRepository = $tokenRepository;
        $this->security        = $security;
    }

    public function getTokensForUser() : array
    {
        return $this->tokenRepository->getTokensForUser(
            $this->security->getUser()->getUsername()
        );
    }

    public function findTokenByNameForUser(string $tokenName) : ?Token
    {
        return $this->tokenRepository->findTokenByNameForUser(
            $this->security->getUser()->getUsername(),
            $tokenName
        );
    }

    public function createTokenForUser(string $tokenName) : Token
    {
        $username = $this->security->getUser()->getUsername();

        $token = new Token();
        $token->setName($tokenName);
        $token->setUsername($username);
        $token->setToken(Uuid::uuid4());
        $token->setSecret(Util::encrypt(Util::generate(Token::CLEARTEXT_SECRET_LENGTH), $username));
        $token->setCreatedAt(new \DateTime());

        $this->tokenRepository->save($token);

        return $token;
    }

    public function remove(Token $token)
    {
        $this->tokenRepository->remove($token);
    }

    public function findToken(string $token) : ?Token
    {
        return $this->tokenRepository->findOneByToken($token);
    }

    public function increaseHitCount(Token $token) : int
    {
        $count = $token->incrementHitCount();

        $this->tokenRepository->save($token);

        return $count;
    }
}