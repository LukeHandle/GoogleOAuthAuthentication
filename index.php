<?php
require_once('config.php');
require_once('GoogleOAuthAuthentication.class.php');

$auth_check = new GoogleOAuthAuthentication($oauth2);

$userInfo = $auth_check->authenticationCheck();

if($userInfo) {
    echo("Logged in");
    echo "Hi ".$userInfo['username'];
} else {
    if(isset($_GET['error'])) {
        if($_GET['error'] === "invalid_email") {
            http_response_code(403);
            $noauth_msg = "Invalid email attempted";
        } elseif($_GET['error'] === "access_denied") {
            http_response_code(402);
            $noauth_msg = "Access was not granted";
        } else {
            http_response_code(400);
            $noauth_msg = "Unknown error occurred";
        }
        echo $noauth_msg;
    } else {
    # Let's login
        $auth_check->login("/");
    }
}
die();
?>