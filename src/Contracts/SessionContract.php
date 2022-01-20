<?php

namespace Plataforma13\SphinxSdk\Contracts;

use Plataforma13\SphinxSdk\Services\CodeChallengeService;

interface SessionContract
{
    /**
     * @return string
     */
    public function getSessionIdCrypted();

    /**
     * @param string $sessionIdCrypted
     *
     * @return void
     */
    public function decryptSessionId(string $sessionIdCrypted
    );

    /**
     * @param string               $sessionIdCrypted
     * @param CodeChallengeService $codeChallengeService
     *
     * @return void
     */
    public function recovery(
        string $sessionIdCrypted,
        CodeChallengeService $codeChallengeService
    );

    /**
     * @param string $data
     *
     * @return void
     */
    public function setAccessToken(string $data);

    /**
     * @return string
     */
    public function getAccessToken();

}
