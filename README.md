# Slack Integration

## Introduction

This document describes how one can integrate a Laravel App into Slack. It
provides step-by-step details as to what to do and how to make it work together.

## How-To

### Setup a new Laravel application

```shell script
$ laravel new slack-integration
$ composer require laravel/ui
$ php artisan ui vue --auth
$ npm install
```
The above commands will provide an empty, authentication-aware, Laravel
application.

```sql
create schema slack_integration;
```
This will create the `slack_integration` schema in your RDBMS.

Make sure to update the `.env` file accordingly (`DB_DATABASE`) and make sure
the `DB_PASSWORD` is set correctly.

```shell script
php artisan migrate --step
```
I am fuzzy about the ability to rollback each individual migration, so I always
use `--step`.

Start two shells, and run `npm run watch` in one and `php artisan serve` in the
other.

You should be to see the normal Laravel welcome screen:
![Laravel Home Page](storage/doc/images/laravelHomePage.png)

Go ahead and register, which will ultimately lead to you being logged in.

### Publishing the app as it is

In order to integrate with Slack, we need to have this application available and
accessible via the Internet. Since my home office network is strongly
firewalled, I will publish this application on my Laravel Forge managed server,
by simply adding a new site to Laravel Forge, and setting it to pick this
repository's master branch. I will also create an appropriate database and user,
configure the .env correctly and tell Forge to enable auto-updates.
 
Head over to your trusted DNS service, and add an entry for your new
application:
![Create DNS Record](storage/doc/images/createDnsRecord.png)

Create a new site in Laravel Forge:
![Create new Forge Site](storage/doc/images/createNewSite.png)

After a while, the setup of the new site has completed:
![Site setup is completed](storage/doc/images/siteCreated.png)

Create a new database and a new user:
![Create new database](storage/doc/images/createDatabase.png)

Now link the new site to your application's code repository in Forge by clicking
on the Git Repository button in the dashboard of your newly created site, and
provide the necessary information:
![Link to Git Repository](storage/doc/images/linkToGitRepository.png)

When the installation is finished, change the Deploy Script to the following:
```shell script
cd /home/forge/www.slack-integration.test.itpassion.com
git pull origin master
composer install --no-interaction --prefer-dist --optimize-autoloader

npm install
npm run prod

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service php7.3-fpm reload ) 9>/tmp/fpmlock

if [ -f artisan ]; then
    php artisan migrate --force
fi
```

Edit the environment and make sure the database related settings are correct.

For Slack integration, you will need to have SSL enabled on your site. Forge
makes this very simple, as it can create LetsEncrypt SSL Certificates for any
installed site.
![Create SSL Certificate](storage/doc/images/createSslCertificate.png)

Deploy the application by clicking the "Deploy Now" button in the "Deployment"
section on the site "Apps" dashboard.

When the deployment finished successfully, visit the website you've just set up.
Register a user, and make sure you can login.

### Slack Integration

Slack integration itself is explained
[here](https://api.slack.com/interactivity/slash-commands).
 
First of all, create a Slack application:
![Create Slack Application](storage/doc/images/createSlackApp.png)
 
Then in the application's management dashboard, click the Slash Commands link
and create a new command
![Create Slash Command Link](storage/doc/images/clickSlashCommandsLink.png)
![Create New Command](storage/doc/images/createNewCommand.png)
 
We do need a request URL where Slack can POST information to. We will use
https://www.slack-integration.test.itpassion.com/slack/slash/command.
Our web application does not have this route configured yet, but we will get
there in a minute.

For now, just provide the required information and click "Save". Be sure you
enable "Escape channels, users, and links sent to your app". 

### Setup the route in the Laravel Application

We need a Controller that will to the `/slack/slash/command` route.

```shell script
php artisan make:controller SlashCommandController
```

In your `routes/web.php` include the following route
```php
Route::post('/slack/slash/command', 'SlashCommandController@execute')
    ->name('slashCommand.execute')
    ->middleware('guest');
```

Write the `execute` member of the `SlashCommandController`:
```php
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
```

Include the URI `/slack/slash/command` in the `$except` array in the
`VerifyCsrfToken` middelware:
```php
    protected $except = [
        '/slack/slash/command'
    ];
```

Push these changes to git, and make sure the application is deployed
automatically by Forge.

Next, in the application management dashboard on Slack, mare sure to install the
application into the desired Slack workspace.

Then, in Slack, type
```slack
/slack-integration subcommand options arguments
```

You will see in the `laravel.log` that Slack was successful submitting the
information to your Web Application:
```text
[2020-02-14 13:04:59] production.INFO: App\Http\Controllers\SlashCommandController::execute  
[2020-02-14 13:04:59] production.DEBUG: Slack sent us the following information:  
[2020-02-14 13:04:59] production.DEBUG: "token" = "J4cXFWHeQzNavnKOA6PZ7kC5"  
[2020-02-14 13:04:59] production.DEBUG: "team_id" = "TU2DX68G7"  
[2020-02-14 13:04:59] production.DEBUG: "team_domain" = "itpassionltd"  
[2020-02-14 13:04:59] production.DEBUG: "channel_id" = "CU2Q4S63G"  
[2020-02-14 13:04:59] production.DEBUG: "channel_name" = "slack-integration"  
[2020-02-14 13:04:59] production.DEBUG: "user_id" = "UU0HZ2A1W"  
[2020-02-14 13:04:59] production.DEBUG: "user_name" = "guus.leeuw"  
[2020-02-14 13:04:59] production.DEBUG: "command" = "/slack-integration"  
[2020-02-14 13:04:59] production.DEBUG: "text" = "command options argument"  
[2020-02-14 13:04:59] production.DEBUG: "response_url" = "https://hooks.slack.com/commands/TU2DX68G7/941203127267/D4Gy8JftTKK1jS2Rgufra81T"  
[2020-02-14 13:04:59] production.DEBUG: "trigger_id" = "952691517893.954473212551.4e6d9cc10a41abf89d9bb8cce5872e0f"  
[2020-02-14 13:04:59] production.DEBUG: We are not doing anything with this command at this moment.  
```

### Mapping Slack users to Application users

Unless your application is going to a benign information that provides public
knowledge (such as a weather report app), you want to make sure that you can
identify the Slack user and map that to a `User` model inside your application,
so that the application can run the command under the appropriate authorization
levels.

First of all, the Controller responding to the in-coming Slash Command must
verify whether or not  
