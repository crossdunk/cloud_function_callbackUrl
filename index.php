<?php
require_once __DIR__.'/vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Google\Cloud\Logging\LoggingClient;
use GuzzleHttp\Client;

function index(ServerRequestInterface $request)
{
    $projectId = getenv('PROJECT_ID');

    $getText = json_encode($_GET);
    $postText = json_encode($_POST);
    $contentText = file_get_contents('php://input');
    $serverText = json_encode($_SERVER);

    $text = "get:{$getText},post:{$postText},contentText:{$contentText},server:{$serverText}";

    if (!empty($projectId)) {
        $logging = new LoggingClient([
            'projectId' => $projectId
        ]);
        $logName = 'callback';
        $logger = $logging->logger($logName);
        $entry = $logger->entry($text);
        $logger->write($entry);
    }

    if (!empty(getenv('SLACK_CHANNEL_URL'))) {
        $client = new Client();
        $client->post(getenv('SLACK_CHANNEL_URL'), [
            \GuzzleHttp\RequestOptions::JSON => ['text' => $text]
        ]);
    }

    return 'ok';
}
