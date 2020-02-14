<?php

namespace App\Http\Controllers;

use App\Concerns\BindsSlackUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SlashCommandController extends Controller
{
    use BindsSlackUser;

    public function execute(Request $request)
    {
        Log::info(__METHOD__);

        if(($response = $this->actAsSlackUser($request)) !== TRUE) {
            return $response;
        }

        Log::debug('Slack sent us the following information:');
        $input = $request->all();
        foreach($input as $key => $value) {
            Log::debug('"' . $key . '" = "' . $value . '"');
        }

        // Execute the command...

        return response('Your registered email is: "' . Auth::user()->email .
            '"', 200);
    }
}
