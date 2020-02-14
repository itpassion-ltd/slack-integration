<?php

namespace App\Http\Controllers;

use App\SlackNonce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SlackBindUserController extends Controller
{
    public function bindUser(string $nonce, Request $request)
    {
        Log::info(__METHOD__);

        Log::debug('Several things:');
        Log::debug('1. Since the user is now authenticated, we can use `Auth::user()`');
        $user = Auth::user();
        Log::debug('     => User #:' . $user->id);
        Log::debug('2. We can find the Slack User ID by finding $nonce in the slack_nonces table');
        $slackNonce = SlackNonce::where('nonce', $nonce)->first();
        if($slackNonce) {
            Log::debug('      => Slack User ID: "' .
                $slackNonce->slack_user_id . '"');
            $user->slack_user_id = $slackNonce->slack_user_id;
            $user->save();
            $slackNonce->delete();
            Log::debug('Updated user and deleted the corresponding mapping ' .
                'record');
        }

        return response('Your Slack account have been mapped to your ' .
            'Slack-Integration account. You can now resubmit your initial ' .
            'request!');
    }
}
