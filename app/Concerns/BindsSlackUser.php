<?php

namespace App\Concerns;

use App\SlackNonce;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

trait BindsSlackUser
{
    /**
     * @param Request $request
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function actAsSlackUser(Request $request)
    {
        Log::info(__METHOD__);

        $slackUserId = $request->input('user_id', NULL);
        Log::debug('User ID received from Slack: "' . $slackUserId . '"');
        if(!$slackUserId) {
            Log::debug("  => Oops!!");
            abort(422, 'Slack did not send the "user_id" across...');
        }

        $user = User::where('slack_user_id', $slackUserId)->first();
        if(!$user) {
            Log::debug('Slack user "' . $slackUserId . '" has not been bound ' .
                'to an internal user record...');
            return $this->createNonce($slackUserId);
        } else {
            Log::debug('This is a known user with ID #' . $user->id);
            Auth::login($user);

            return TRUE;
        }
    }

    /**
     * Create a nonce for the given Slack User ID and abort with a 200 code and
     * the route that Slack should send to the user in order to make sure the
     * user authenticates within our app.
     *
     * @param string $slackUserId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function createNonce(string $slackUserId)
    {
        SlackNonce::where('slack_user_id', $slackUserId)->delete();

        $nonce = Uuid::uuid4()->toString();
        SlackNonce::create([
            'nonce' => $nonce,
            'slack_user_id' => $slackUserId
        ]);

        return response([
            'response_type' => 'ephemeral',
            'text' => route('slack-nonce', [
                'nonce' => $nonce
            ])
        ]);
    }
}
