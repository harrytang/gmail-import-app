<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2018 Power Kernel
 */
use google\appengine\api\cloud_storage\CloudStorageTools;

if (isset($_GET['type']) && $_GET['type'] == 'test') {
    $default_bucket = CloudStorageTools::getDefaultGoogleStorageBucketName();
    var_dump($default_bucket);
    $cri=file_get_contents("gs://${default_bucket}/credentials.json");
    var_dump($cri);
    file_put_contents("gs://${default_bucket}/credentials1.json", "Welcome");
    $client = getClient();;
}

if (isset($_GET['type']) && $_GET['type'] == 'mime') {
    if (isset($_POST['body-mime'])) {
        require __DIR__ . '/auth.php';
        require __DIR__ . '/import.php';

        // API client
        $client = getClient();

        // get and save message
        $mime = $_POST['body-mime'];
        $raw = rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');


        // Import
        try {
            $bytes = mb_strlen($raw, '8bit');
            if ($bytes > (1 * 1024 * 1024)) {
                importBig($mime, $client);
            } else {
                importSmall($raw, $client);
            }
        } catch (Google_Service_Exception $e) {
            http_response_code(406);
            throw new ErrorException('Google Service Error', 406);
        }

    }
}
