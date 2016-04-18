<?php

namespace FeddScore\Http\Middleware;

use Closure;

use \NCSU\Auth\Adapter\ShibAuthAdapter;
use \NCSU\Auth\AuthService;
use \NCSU\Auth\Http\Request;

class ShibAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $shibRequest = Request::createFromGlobals();

        $shibAuthAdapter = new ShibAuthAdapter($shibRequest);
        $service = new AuthService($shibAuthAdapter);
        $result = $service->authenticate();

        $identity = $service->getIdentity();
        if ($this->isNotAuthorized($identity)) {
            abort(403);
        }

        return $next($request);
    }

    private function isNotAuthorized($identity)
    {
        $allowedUsers = explode(' ', env('ALLOWED_USERS'));

        return !in_array($identity, $allowedUsers);

    }

    private function beShibbolethUser($userId)
    {
        $_SERVER['Shib-Session-ID'] = (($userId !== '') ? 'foo' : null);
        $_SERVER['SHIB_UID'] = $userId;
    }
}
