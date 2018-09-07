<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2018 Power Kernel
 */

if (isset($_GET['type']) && $_GET['type'] == 'mime') {
    if (isset($_POST['body-mime']) || $_GET['body-mime']) {
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
