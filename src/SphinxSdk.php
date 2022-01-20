<?php

namespace Plataforma13\SphinxSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Plataforma13\SphinxSdk\Contracts\SphinxContract;
use Plataforma13\SphinxSdk\Exceptions\SphinxSdkException;
use Plataforma13\SphinxSdk\Services\CodeChallengeService;
use Plataforma13\SphinxSdk\Services\CodeStateService;

class SphinxSdk implements SphinxContract
{
    protected const URL_AUTHORIZE = '%s://%s/oauth/authorize?%s';
    protected const URL_LOGOUT = '%s://%s/oauth/logout?%s';
    protected const URL_TOKEN = '%s://%s/oauth/token';
    protected const URL_USERINFO = '%s://%s/api/user';

    public static $userModel = 'Plataforma13\SphinxSdk\Models\User';
    public static $logChannel = 'single';

    public function __construct(
        CodeStateService $codeStateService,
        CodeChallengeService $codeChallengeService
    ) {
        $this->codeStateService = $codeStateService;
        $this->codeChallengeService = $codeChallengeService;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return sprintf(
            self::URL_AUTHORIZE,
            $this->getHttpProcol(),
            config('sphinx.host'),
            http_build_query([
                'client_id'             => config('sphinx.client_id'),
                'redirect_url'          => config('sphinx.redirect_url'),
                'response_type'         => 'code',
                'state'                 => $this->codeStateService->generate(),
                'code_challenge'        => $this->codeChallengeService->generate(
                ),
                'code_challenge_method' => $this->codeChallengeService->getMethod(
                ),
            ])
        );
    }

    /**
     * @return string
     */
    protected function getTokenUrl()
    {
        return sprintf(
            self::URL_TOKEN,
            $this->getHttpProcol(),
            config('sphinx.host')
        );
    }

    /**
     * @return string
     */
    protected function getUserInfoUrl()
    {
        return sprintf(
            self::URL_USERINFO,
            self::getHttpProcol(),
            config('sphinx.host')
        );
    }


    /**
     * @return string
     */
    protected function getLogoutUrl()
    {
        return sprintf(
            self::URL_LOGOUT,
            $this->getHttpProcol(),
            config('sphinx.host')
        );
    }


    /**
     * @param Request $request
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array|mixed|null
     */
    public function handleLoginCallback(Request $request)
    {
        try {
            $code = $request->input('code') ?? null;
            $state = $request->input('state') ?? null;


            $this->codeStateService->validate(
                $state,
                $this->codeChallengeService
            );

            try {
                /**
                 * Requesting access token to Sphinx
                 */
                $guzzleClient = new Client();
                $response = $guzzleClient->request(
                    'POST',
                    $this->getTokenUrl(),
                    [
                        'form_params' => [
                            'grant_type'    => 'authorization_code',
                            'client_id'     => config('sphinx.client_id'),
                            'client_secret' => config('sphinx.secret'),
                            'redirect_url'  => config('sphinx.redirect_url'),
                            'code_verifier' => $this->codeChallengeService->getChallengePrivate(
                            ),
                            'code'          => $code
                        ]
                    ]
                );

                $response = json_decode(
                    $response->getBody()->getContents(),
                    true
                );

                if (!isset($response['access_token'])
                    or !isset($response['refresh_token'])
                ) {
                    throw new SphinxSdkException('Access Token not found.');
                }

                /**
                 * Recoverying user data
                 */
                $response['user'] = $this->getUserInfo(
                    $response['access_token']
                );

                return $response;
            } catch (ClientException $e) {
                $response = json_decode(
                    $e->getResponse()->getBody()->getContents(),
                    true
                );

                if (isset($response['error'])) {
                    throw new SphinxSdkException(
                        $response['error_description'] ?? 'Unknown error.'
                    );
                }
            }
        } catch (SphinxSdkException $e) {
            $this->notifyLog($e);
        } catch (\Throwable $e) {
            $this->notifyLog($e);
        }
        return null;
    }


    /**
     * getUser
     *
     * @param null $accessToken
     *
     * @throws \Exception
     * @return object
     */
    protected function getUserInfo(string $accessToken = null)
    {
        if (empty($accessToken)) {
            throw new SphinxSdkException('Require Access Token: empty.');
        }

        try {
            $guzzleClient = new Client();
            $response = $guzzleClient->request(
                'GET',
                $this->getUserInfoUrl(),
                [
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken,
                    ]
                ]
            );

            $response = json_decode(
                $response->getBody()->getContents(),
                true
            );

            if ((!isset($response['id']) and !isset($response['_id']))
                or !isset($response['email'])
            ) {
                throw new SphinxSdkException('User data not found.');
            }

            return $response;
        } catch (ClientException $e) {
            $response = json_decode(
                $e->getResponse()->getBody()->getContents(),
                true
            );

            if (isset($response['error'])) {
                throw new SphinxSdkException(
                    $response['error_description'] ?? 'Unknown error.'
                );
            }
        }
    }


    /**
     * @return string
     */
    protected function getHttpProcol()
    {
        return env('APP_ENV') != 'production' ? 'http' : 'https';
    }

    /**
     * Set the user model class name.
     *
     * @param string $clientModel
     *
     * @return void
     */
    public static function setUserModel($userModel)
    {
        static::$userModel = $userModel;
    }

    /**
     * Get the user model class name.
     *
     * @return string
     */
    public static function getUserModel()
    {
        return static::$userModel;
    }

    /**
     * @param $logChannel
     */
    public static function setLogChannel($logChannel)
    {
        static::$logChannel = $logChannel;
    }

    /**
     * @return string
     */
    public static function getLogChannel()
    {
        return static::$logChannel;
    }

    /**
     * @param array<mixed> $data
     *
     * @return bool
     */
    public function validateLoginUserModel(array $data)
    {
        $model = static::getUserModel();
        return (new $model)->findUser($data);
    }

    /**
     * @return Log|\Psr\Log\LoggerInterface
     */
    public static function log()
    {
        return Log::channel(static::getLogChannel());
    }

    /**
     * @param Exception $e
     */
    public function notifyLog(\Throwable $e) {
        $message = sprintf("\n*** SphinxSdk\nFile %s\nLine %d\nMessage %s\n",
            $e->getFile(),
            $e->getLine(),
            $e->getMessage()
        );

        SphinxSdk::log()->critical($message);
    }
}
