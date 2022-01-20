<?php

namespace Plataforma13\SphinxSdk\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Session;
use Plataforma13\SphinxSdk\Contracts\SessionContract;

class SessionService implements SessionContract
{
    protected const SESSION_ACCESS_TOKEN = 'oauth2_access_token';

    /**
     * @return string
     */
    public function getSessionIdCrypted()
    {
        return strtr(
            rtrim(
                base64_encode(encrypt(Session::getId()))
                ,
                '='
            ),
            '+/',
            '-_'
        );
    }

    /**
     * @param string $sessionIdCrypted
     *
     * @return mixed|string
     */
    public function decryptSessionId(string $sessionIdCrypted
    ) {
        try {
            if (empty($sessionIdCrypted)) {
                throw new \InvalidArgumentException(
                    'Code Challenge invalid or not found.'
                );
            }

            return decrypt(
                base64_decode(strtr($sessionIdCrypted, '-_', '+/') . '=')
            );
        } catch (DecryptException $e) {
            throw new DecryptException($e->getMessage());
        }
    }

    /**
     * @param string               $sessionIdCrypted
     * @param CodeChallengeService $codeChallengeService
     */
    public function recovery(
        string $sessionIdCrypted,
        CodeChallengeService $codeChallengeService
    ) {
        $sessionId = $this->decryptSessionId(
            $sessionIdCrypted
        );

        Session::setId($sessionId);
        Session::start();

        $challengePrivate = $codeChallengeService->getChallengePrivate();

        if (empty($challengePrivate)) {
            throw new \InvalidArgumentException(
                'Code Challenge invalid or not found.'
            );
        }
    }

    /**
     * @param string $data
     */
    public function setAccessToken(string $data)
    {
        Session::put(
            self::SESSION_ACCESS_TOKEN,
            encrypt($data)
        );
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return decrypt(Session::get(self::SESSION_ACCESS_TOKEN));
    }
}
