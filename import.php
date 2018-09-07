<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2018 Power Kernel
 */

/**
 * import small message
 * @param $raw
 * @param $client
 */
function importSmall($raw, $client){
    // Get the API client and construct the service object.
    $service = new Google_Service_Gmail($client);

    // Gmail Message
    $message = new Google_Service_Gmail_Message();
    $message->raw = $raw;
    $message->labelIds = ['UNREAD', 'INBOX']; //  "IMPORTANT","CATEGORY_PERSONAL", "SENT"

    // API call
    $r=$service->users_messages->import('me', $message);
    echo json_encode($r);
}

/**
 * import big message
 * @param $mime
 * @param $client
 */
function importBig($mime, $client){
    // Get the API client and construct the service object.
    $client->setDefer(true);
    $service = new Google_Service_Gmail($client);

    // Gmail Message
    $message = new Google_Service_Gmail_Message();
    $message->labelIds = ['UNREAD', 'INBOX']; //  "IMPORTANT","CATEGORY_PERSONAL", "SENT"

    //resumable upload
    $chunkSizeBytes = 1 * 1024 * 1024;
    $request = $service->users_messages->import('me', $message);
    $media = new Google_Http_MediaFileUpload(
        $client,
        $request,
        'message/rfc822',
        $mime,
        true,
        $chunkSizeBytes
    );
    $media->setFileSize(strlen($mime));
    $status = false;
    while (!$status) {
        $status = $media->nextChunk();
    }
    $client->setDefer(false);
    echo json_encode($status);
}
