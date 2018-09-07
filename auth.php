<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2018 Power Kernel
 */
# Imports the Google Cloud client library
use google\appengine\api\cloud_storage\CloudStorageTools;


error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once __DIR__ . '/vendor/autoload.php';



$default_bucket = CloudStorageTools::getDefaultGoogleStorageBucketName();
define('APPLICATION_NAME', 'Gmail API');
define('CREDENTIALS_PATH', "gs://${default_bucket}/credentials.json");
define('CLIENT_SECRET_PATH', "gs://${default_bucket}/secret.json");
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
    // app config
    $config = json_decode(file_get_contents(CLIENT_SECRET_PATH), true);

    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfig($config);
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.


    if (file_exists(CREDENTIALS_PATH)) {
        $accessToken = json_decode(file_get_contents(CREDENTIALS_PATH), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        file_put_contents(CREDENTIALS_PATH, json_encode($accessToken));
        printf("Credentials saved to %s\n", CREDENTIALS_PATH);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents(CREDENTIALS_PATH, json_encode($client->getAccessToken()));
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
