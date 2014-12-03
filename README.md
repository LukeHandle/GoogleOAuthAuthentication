# GoogleOAuthAuthentication

## Why
For adding authentication without the hassle of dealing with usernames/passwords. It is hard-coded to take an domain to authenticate against (assumed using GApps).

## Should I use it?
I don't - at least, not anymore. I switched to https://github.com/bitly/google_auth_proxy because I needed to access a few endpoints and they needed to hook into the same authentication. Short of ripping them apart and mangling my own auth into them, the reverse proxy route was better suited. 

### And anyway
An imperfect solution that had issues with cookies when used in conjuction with some other PHP scripts. It mostly had problems with the session handling. I made attempts to respect existing ones but they weren't always effective.

## How to use
Basically, set up an OAuth application and stick the details into the `config.php` along with filling out the other fields.

When wanting to make an Auth check, include the config and class before making a new `GoogleOAuthAuthentication` object:
    
    $auth_check = new GoogleOAuthAuthentication($oauth2);
    $userInfo = $auth_check->authenticationCheck(true);

`$userInfo` returns false when they are not authenticated, and an object on success:

    $userInfo = array(
        'access_token' => 'OAuth access token',
        'id' => 'Google Profile ID',
        'email' => 'Full email of account',
        'username' => 'Just the username of account'
    );


## Google OAuth Setup

You will need to register an OAuth application with google, and configure it with Redirect URI(s) for the domain you
intend to run this on.

1. Create a new project: https://console.developers.google.com/project
2. Under "APIs & Auth", choose "Credentials"
3. Now, choose "Create new Client ID"
   * The Application Type should be **Web application**
   * Enter your domain in the Authorized Javascript Origins `https://example.com`
   * Enter the correct Authorized Redirect URL `https://example.com/oauth2callback`
4. Under "APIs & Auth" choose "Consent Screen"
   * Fill in the necessary fields and Save (this is _required_)
5. Take note of the **Client ID** and **Client Secret**

(courtsey of https://github.com/bitly/google_auth_proxy)

