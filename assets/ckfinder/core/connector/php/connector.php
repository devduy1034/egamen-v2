<?php
require_once __DIR__ . '/vendor/autoload.php';
use CKSource\CKFinder\CKFinder;
use CKSource\CKFinder\Event\CKFinderEvent;
$ckfinder = new CKFinder(__DIR__ . '/../../../config.php');
$ckfinder->on(CKFinderEvent::BEFORE_COMMAND_FILE_UPLOAD, function(CKFinderEvent $app) {
    $workingFolder = $app->getContainer()->getWorkingFolder();
    $currentFolderPath = $workingFolder->getPath();
    $backend = $workingFolder->getBackend();
    $maxFile = $app->getContainer()->getConfig()->get('backends')['default']['maxFilesDir'];
    $files = $backend->listContents($currentFolderPath, true);
    $imageCount = 0;
    foreach ($files as $file) $imageCount++;
    if ($imageCount >= $maxFile) {
        header('Content-Type: application/json');
        echo json_encode(array(
            'error' => [
                'message' => 'Số lượng file trong thư mục đã tối đa. Vui lòng tạo thư mục mới và upload!'
            ]
        ));
        die();
    }
    return true;
});
$ckfinder->run();