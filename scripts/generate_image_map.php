<?php
// Scans public/img and creates config/image_map.php mapping normalized product names
// to the exact image path (folder + filename).

function normalize_text_local($text) {
    $text = mb_strtolower(trim($text), 'UTF-8');
    if (function_exists('transliterator_transliterate')) {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII;', $text);
    } else {
        $text = @iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;
    }
    $text = preg_replace('/[^a-z0-9 ]+/i', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    return $text;
}

$root = realpath(__DIR__ . '/../public');
if (!$root) {
    echo "Cannot locate public/\n";
    exit(1);
}

$imgRoot = $root . DIRECTORY_SEPARATOR . 'img';
if (!is_dir($imgRoot)) {
    echo "No img directory\n";
    exit(1);
}

$map = [];
$folders = array_filter(scandir($imgRoot), function ($d) use ($imgRoot) {
    return $d !== '.' && $d !== '..' && is_dir($imgRoot . DIRECTORY_SEPARATOR . $d);
});

foreach ($folders as $folder) {
    $folderPath = $imgRoot . DIRECTORY_SEPARATOR . $folder;
    $files = array_values(array_filter(scandir($folderPath), function ($f) use ($folderPath) {
        return $f !== '.' && $f !== '..' && is_file($folderPath . DIRECTORY_SEPARATOR . $f);
    }));

    foreach ($files as $file) {
        $key = normalize_text_local(pathinfo($file, PATHINFO_FILENAME));
        // store mapping; if duplicate key across folders, prefer folder match already present
        if (!isset($map[$key])) {
            $map[$key] = '/img/' . str_replace('\\', '/', $folder) . '/' . $file;
        }
    }
}

$outDir = realpath(__DIR__ . '/../config') ?: __DIR__ . '/../config';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

$outFile = $outDir . DIRECTORY_SEPARATOR . 'image_map.php';
$export = var_export($map, true);
$content = "<?php\n// Auto-generated image map. Do not edit unless you know what you're doing.\nreturn " . $export . ";\n";
file_put_contents($outFile, $content);

echo "Written: " . $outFile . " (" . count($map) . " entries)\n";
