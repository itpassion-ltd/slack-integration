<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlashCommandController extends Controller
{
    public function execute(Request $request)
    {
        Log::info(__METHOD__);

        Log::debug('Slack sent us the following information:');
        $input = $request->all();
        foreach($input as $key => $value) {
            Log::debug('"' . $key . '" = "' . $value . '"');
        }

        Log::debug('We are not doing anything with this command at this ' .
            'moment.');

        return response('', 200);
    }
}
