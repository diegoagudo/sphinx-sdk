<?php

namespace Plataforma13\SphinxSdk\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Plataforma13\SphinxSdk\Contracts\CodeStateContract;

class CodeStateService implements CodeStateContract
{
    protected const SESSION_CODE_STATE = 'oauth2_state';
    protected const CODE_STATE_SEPARATOR = '.';

    /**
     * @param SessionService $sessionService
     */
    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $state = sprintf(
            '%s%s%s',
            Str::random(256),
            self::CODE_STATE_SEPARATOR,
            /**
             * É necessário passar a Session ID junto ao Code State, pois quando
             * recebemos uma redirect 302 de uma aplicação externa, se inicia uma nova sessão.
             * Esta sessão será recuperada em SessionService->recovery()
             */
            $this->sessionService->getSessionIdCrypted()
        );

        Session::put(self::SESSION_CODE_STATE, $state);

        return $state;
    }


    /**
     * @return mixed|string
     */
    public function getCodeState()
    {
        return Session::get(self::SESSION_CODE_STATE);
    }

    /**
     * @param string               $codeStateSessionId
     * @param CodeChallengeService $codeChallengeService
     *
     * @throws \Exception
     */
    public function validate(
        string $codeStateSessionId,
        CodeChallengeService $codeChallengeService
    ) {
        if (empty($codeStateSessionId)) {
            throw new \InvalidArgumentException(
                'Code State invalid or not found.'
            );
        }

        if (strpos($codeStateSessionId, self::CODE_STATE_SEPARATOR)
            === false
        ) {
            throw new \InvalidArgumentException('Code State format invalid.');
        }

        $sessionIdCrypted = ltrim(
            strstr(
                $codeStateSessionId,
                self::CODE_STATE_SEPARATOR,
                false
            ),
            self::CODE_STATE_SEPARATOR
        );

        $this->sessionService->recovery(
            $sessionIdCrypted,
            $codeChallengeService
        );

        if (empty($this->getCodeState())) {
            throw new \InvalidArgumentException(
                'Code State invalid or not found.'
            );
        }

        if ($codeStateSessionId != $this->getCodeState()) {
            throw new \UnexpectedValueException('Code State mismatch.');
        }
    }
}
