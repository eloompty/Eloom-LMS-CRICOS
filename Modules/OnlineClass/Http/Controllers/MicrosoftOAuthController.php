<?php

namespace Modules\OnlineClass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MicrosoftOAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user,trainer');
    }

    public function redirect($id, $type)
    {
        $clientId = getSettingValue('microsoft_client_id');
        $redirectUri = getSettingValue('microsoft_redirect_uri');
        $tenant_url = getSettingValue('microsoft_tenant_url');
        $scopes = 'User.Read Mail.Read Calendars.ReadWrite offline_access';

        // Guard the callback against cross-site request forgery
        $state = Str::random(40);
        session(['microsoft_oauth_state' => $state, 'id' => $id, 'type' => $type]);

        $authUrl = "https://login.microsoftonline.com/{$tenant_url}/oauth2/v2.0/authorize?client_id={$clientId}"
            . "&response_type=code&redirect_uri=" . urlencode($redirectUri)
            . "&response_mode=query&scope=" . urlencode($scopes)
            . "&state={$state}";

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $expectedState = session()->pull('microsoft_oauth_state');

        if (!$expectedState || !hash_equals($expectedState, (string) $request->query('state'))) {
            return redirect('/')->with('failure', 'The Microsoft sign-in request expired. Please try again.');
        }

        $code = $request->query('code');
        $clientId = getSettingValue('microsoft_client_id');
        $clientSecret = getSettingValue('microsoft_client_secret');
        $tenant_url = getSettingValue('microsoft_tenant_url');
        $redirectUri = getSettingValue('microsoft_redirect_uri');

        $tokenRequestUrl = "https://login.microsoftonline.com/{$tenant_url}/oauth2/v2.0/token";
        $response = Http::asForm()->post($tokenRequestUrl, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        $accessToken = $response->json('access_token');

        if (!$accessToken) {
            Log::error('MicrosoftOAuthController->callback : ' . $response->body());

            return redirect('/')->with('failure', 'Microsoft did not return an access token.');
        }

        $id = session('id');
        $type = session('type');

        // Store the access token in the session for the next step of the flow
        session(['microsoft_access_token' => $accessToken, 'id' => $id, 'type' => $type]);

        if ($type == 'group') {
            return view('onlineclass::group.teams.create');
        } elseif ($type == 'intake_unit') {
            return view('intake::course.unit.teams.create');
        } elseif ($type == 'trainer') {
            return view('trainer::trainer.unit.teams.create');
        } elseif ($type == 'trainer_group') {
            return view('trainer::trainer.onlinegroup.teams.create');
        }

        return redirect('/');
    }
}
