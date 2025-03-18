<?php
header('Content-Type: application/json; charset=UTF-8');

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

define('CACHE_DIR', __DIR__ . '/cache/');
define('CACHE_LIFETIME', 60);

if (!file_exists(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

function findAndViewFiles($fileName) {
    $baseDir = '.';
    $response = [
        'status' => 'error',
        'message' => '',
        'data' => []
    ];
    if (!chdir($baseDir)) {
        $response['message'] = "Failed to change directory to $baseDir.";
        return $response;
    }
    if (!file_exists($fileName)) {
        $response['message'] = "File $fileName does not exist.";
        return $response;
    }
    $content = file_get_contents($fileName);
    $decodedContent = mb_convert_encoding($content, 'UTF-8', 'auto');
    preg_match('/const\s+array<string>\s+levelNames\s*=\s*{([^}]*)}/u', $decodedContent, $matches);
    if (!isset($matches[1])) {
        $response['message'] = "No level names found in the file.";
        return $response;
    }
    $levelNames = array_map('trim', explode(',', str_replace('"', '', $matches[1])));
    $levelNames = array_map(function($name) {
        return mb_convert_encoding($name, 'UTF-8', 'auto');
    }, $levelNames);
    $files = glob('*uniText -*.asdat');
    $foundFiles = [];
    foreach ($levelNames as $levelName) {
        foreach ($files as $file) {
            if (stripos($file, $levelName) !== false) {
                $filePath = "$baseDir/$file";
                if (file_exists($filePath)) {
                    $fileContent = file_get_contents($filePath);
                    if (mb_detect_encoding($fileContent, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true)) {
                        $decodedFileContent = mb_convert_encoding($fileContent, 'UTF-8', ['UTF-8', 'ISO-8859-1', 'Windows-1252']);
                    } else {
                        $decodedFileContent = utf8_encode($fileContent);
                    }
                    $foundFiles[$file] = $decodedFileContent;
                }
            }
        }
    }
    if (!empty($foundFiles)) {
        $response['status'] = 'success';
        $response['message'] = 'Matching files found.';
        $response['data'] = $foundFiles;
    } else {
        $response['message'] = "No matching .asdat files found.";
    }
    return $response;
}

function getLatestCacheFile() {
    $files = glob(CACHE_DIR . '*.json');
    if (empty($files)) return null;
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    return $files[0];
}

function serveCache($fileName) {
    $latestCache = getLatestCacheFile();
    if ($latestCache) {
        readfile($latestCache);
    } else {
        echo json_encode(['status' => 'loading', 'message' => 'Generating new cache...'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    $lockFile = CACHE_DIR . 'cache.lock';
    if (!file_exists($lockFile)) {
        touch($lockFile);
        try {
            $response = findAndViewFiles($fileName);
            $newCacheFile = CACHE_DIR . 'cache_' . time() . '.json';
            file_put_contents($newCacheFile, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $oldCaches = glob(CACHE_DIR . '*.json');
            foreach ($oldCaches as $cache) {
                if ($cache !== $newCacheFile) {
                    unlink($cache);
                }
            }
        } finally {
            unlink($lockFile);
        }
    }
}

serveCache('pntests-voting.mut');
?>
