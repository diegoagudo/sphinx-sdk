<?php

namespace Plataforma13\SphinxSdk\Contracts;

use Plataforma13\SphinxSdk\Services\CodeChallengeService;

interface CodeStateContract
{
    /**
     * @return string
     */
    public function generate();

    /**
     * @return string
     */
    public function getCodeState();

    /**
     * @param string               $codeStateSessionId
     * @param CodeChallengeService $codeChallengeService
     *
     * @return void
     */
    public function validate(
        string $codeStateSessionId,
        CodeChallengeService $codeChallengeService
    );

}
