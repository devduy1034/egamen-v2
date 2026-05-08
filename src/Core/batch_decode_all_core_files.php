<?php

function freads($O000O0O00, $OO00O0000, $OOO0O0O00) {
    $OO00O00O0 = str_replace(
        fread($O000O0O00, $OO00O0000+526),
        '',
        fread($O000O0O00, filesize($OOO0O0O00))
    );
    return strtr(
        base64_decode($OO00O00O0),
        'trong20SKD163AMaGNOHPBbCcdEeFfIiJjkL89lmQqRhsTpUuVvWwXxYyZz457/+=',
        'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/='
    );
}

function getAllPhpFiles($directory) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

function decodeFile($filePath, $maxLayer = 10) {
    $basename = basename($filePath);
    
    if (strpos($basename, 'batch_decode') !== false || 
        strpos($basename, 'decode_multilayer') !== false) {
        return false;
    }
    
    $inputFile = $filePath;
    $content = '';
    $tempFile = dirname($filePath) . '/tmp_layer_' . uniqid() . '.php';
    
    try {
        for ($i = 0; $i < $maxLayer; $i++) {
            if (!file_exists($inputFile)) {
                break;
            }
            
            $fp = fopen($inputFile, 'rb');
            if (!$fp) {
                break;
            }
            
            $decoded = freads($fp, 405, $inputFile);
            fclose($fp);
            
            if ($decoded === $content || trim($decoded) === '' || strpos($decoded, 'freads') === false) {
                $content = $decoded;
                break;
            }
            
            $content = $decoded;
            
            if ($i < $maxLayer - 1) {
                file_put_contents($tempFile, $content);
                $inputFile = $tempFile;
            }
        }
        
        $content = preg_replace('/^.*?\*\/(\s*)/s', '', $content, 1); // Xóa từ đầu đến sau dấu */
        
        $content = ltrim($content);
        if (strpos($content, '<?php') !== 0) {
            $content = "<?php\n" . $content;
        }
        
        file_put_contents($filePath, $content);
        
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        
        return true;
        
    } catch (Exception $e) {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        return false;
    }
}

$coreDir = __DIR__;
echo "Bắt đầu giải mã tất cả file PHP trong: $coreDir\n";
echo "==============================================\n";

$allFiles = getAllPhpFiles($coreDir);

$successCount = 0;
$errorCount = 0;

foreach ($allFiles as $file) {
    $relativePath = str_replace($coreDir . DIRECTORY_SEPARATOR, '', $file);
    
    echo "Đang xử lý: $relativePath ... ";
    
    if (decodeFile($file)) {
        echo "✓ Thành công\n";
        $successCount++;
    } else {
        echo "✗ Bỏ qua\n";
        $errorCount++;
    }
}

echo "\n==============================================\n";
echo "Hoàn tất!\n";
echo "Tổng số file đã xử lý: " . count($allFiles) . "\n";
echo "Thành công: $successCount\n";
echo "Bỏ qua: $errorCount\n";
echo "==============================================\n"; 