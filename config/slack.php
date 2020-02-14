<?php

return [

    /*
     * The Model that is used to bind Slack users to this application's users.
     */
    'model' => \App\User::class,

    /*
     * The field that is used to store the Slack User ID
     */
    'slackUserId' => 'slack_user_id',
];
