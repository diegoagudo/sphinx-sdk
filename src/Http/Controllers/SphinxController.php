<?php

namespace Plataforma13\SphinxSdk\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plataforma13\SphinxSdk\SphinxSdk;

class SphinxController extends Controller
{
    /**
     * @param SphinxSdk $sphinxSdk
     */
    public function __construct(SphinxSdk $sphinxSdk)
    {
        $this->sphinxSdk = $sphinxSdk;
    }

    /**
     * @param Request $request
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function callback(Request $request)
    {
        $data = $this->sphinxSdk->handleLoginCallback($request);

        if(!empty($data)) {
            $user = $this->sphinxSdk->validateLoginUserModel([
                'document' => $data['user']['document']
            ]);

            if ($user) {
                auth()->loginUsingId($user->id);
            }
        }

        return redirect('/');
    }
}
