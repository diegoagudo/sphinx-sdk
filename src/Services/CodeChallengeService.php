<?php

namespace Plataforma13\SphinxSdk\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Plataforma13\SphinxSdk\Contracts\CodeChallengeContract;

class CodeChallengeService implements CodeChallengeContract
{
    protected const SESSION_CHALLENGE_PUBLIC = 'oauth2_code_public';
    protected const SESSION_CHALLENGE_PRIVATE = 'oauth2_code_private';
    protected const CODE_CHALLENGE_METHOD = 'S256';

    /**
     * @return string
     */
    public function generate()
    {
        $codeVerifier = Str::random(128);

        $codeChallenge = strtr(
            rtrim(
                base64_encode(hash('sha256', $codeVerifier, true))
                ,
                '='
            ),
            '+/',
            '-_'
        );

        Session::put(self::SESSION_CHALLENGE_PRIVATE, $codeVerifier);
        Session::put(self::SESSION_CHALLENGE_PUBLIC, $codeChallenge);

        return $codeChallenge;
    }

    /**
     * @return string
     */
    public function getChallengePublic()
    {
        return Session::get(self::SESSION_CHALLENGE_PUBLIC);
    }

    /**
     * @return string
     */
    public function getChallengePrivate()
    {
        return Session::get(self::SESSION_CHALLENGE_PRIVATE);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return self::CODE_CHALLENGE_METHOD;
    }
}
