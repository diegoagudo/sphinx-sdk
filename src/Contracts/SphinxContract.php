<?php

namespace Plataforma13\SphinxSdk\Contracts;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

interface SphinxContract
{
    /**
     * @return string
     */
    public function getLoginUrl();

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function handleLoginCallback(Request $request);

    /**
     * @param string $userModel
     *
     * @return mixed
     */
    public static function setUserModel($userModel);

    /**
     * @return string
     */
    public static function getUserModel();


    /**
     * @param $logChannel
     *
     * @return void
     */
    public static function setLogChannel($logChannel);

    /**
     * @return string
     */
    public static function getLogChannel();


    /**
     * @param array<string> $data
     *
     * @return mixed
     */
    public function validateLoginUserModel(array $data);


    /**
     * @return Log
     */
    public static function log();

}
