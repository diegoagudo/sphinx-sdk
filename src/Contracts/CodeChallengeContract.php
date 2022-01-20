<?php

namespace Plataforma13\SphinxSdk\Contracts;

interface CodeChallengeContract
{
    /**
     * @return string
     */
    public function generate();

    /**
     * @return string
     */
    public function getChallengePublic();

    /**
     * @return string
     */
    public function getChallengePrivate();

    /**
     * @return string
     */
    public function getMethod();
}
