<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlackBindUserController extends Controller
{
    public function bindUser(string $nonce, Request $request)
    {
        Log::info(__METHOD__);

        Log::debug('Several things:');
        Log::debug('1. Since the user is now authenticated, we can use `Auth::user()`');
        Log::debug('2. We can find the Slack User ID by finding $nonce in the slack_nonces table');

        return response('Well done!');
    }
}
