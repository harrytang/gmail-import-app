<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2018 Power Kernel
 */

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Gmail API');
define('CREDENTIALS_PATH', __DIR__.'/credentials/gmail-import.json');
define('CLIENT_SECRET_PATH', __DIR__.'/credentials/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/gmail-php-quickstart.json
define('SCOPES', implode(' ', [
        Google_Service_Gmail::GMAIL_INSERT,
        Google_Service_Gmail::GMAIL_MODIFY,
    ])
);

date_default_timezone_set('America/New_York'); // Prevent DateTime tz exception
#if (php_sapi_name() != 'cli') {
#    throw new Exception('This application must be run on the command line.');
#}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 * @throws Google_Exception
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfig(CLIENT_SECRET_PATH);
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $default_bucket = CloudStorageTools::getDefaultGoogleStorageBucketName();
    $credentialsPath = "gs://${default_bucket}/credentials.json";

    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
if (php_sapi_name() == 'cli') {
    try {
        getClient();
    } catch (Google_Exception $e) {
    }
}
