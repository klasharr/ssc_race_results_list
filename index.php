<?php

require 'vendor/autoload.php';

use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

define('DEFAULT_COUNT', 20);
define('MAX_DISPLAY_COUNT', 50);
define('BASE_URL', 'http://results.swanagesailingclub.org.uk/');
define('YEAR', 2019);

try {

    $count = isset($_GET['count']) && (int) $_GET['count'] < MAX_DISPLAY_COUNT ? (int) $_GET['count'] : DEFAULT_COUNT;

    $res = array(
        'error' => 0,
        'data' => getFiles($count, '../' . YEAR )
    );

} catch (Exception $e) {

    $res = array(
        'error' => 1,
        'data' => $e->getMessage()
    );
}

header('Content-Type: application/json');
echo json_encode($res);


function getFiles($count = DEFAULT_COUNT, $sourceDir = '.'){

    $logger = new Logger('name');
    $logger->pushHandler(new StreamHandler('php://stdout', Logger::ERROR));

    $rec = new Chalky\Processor\DirectoryRecursor(
        $sourceDir,
        $logger,
        array('htm'),
        array('list')
    );

    $recent = new Chalky\Handler\MostRecentFiles(
        $sourceDir,
        $logger,
        array(
            'base_url' => BASE_URL . '/' . YEAR,
            'number' => $count,
        )
    );

    $rec->addFileProcessor($recent);
    $rec->process();

    return $recent->getLatestFiles();

}