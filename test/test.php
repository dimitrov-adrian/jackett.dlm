<?php

if (empty($argv[1]) && empty($argv[2]) && empty($argv[3])) {
  echo "\nUsage: make tests ARGS=\"<username> <password> <keyword>\"\n";
  exit;
}

echo "\n";

require __DIR__ . '/../search.php';
require __DIR__ . '/FakePlugin.php';

/**
 * Convert bytes to human size
 *
 * @param $bytes
 *
 * @return string
 */
function formatBytes($bytes)
{
  $units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
  $bytes = max($bytes, 0);
  $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
  $pow = min($pow, count($units) - 1);
  $bytes /= pow(1024, $pow);
  return round($bytes, 2) . ' ' . $units[$pow];
}

$search = new SynoDLMSearchJackett();
$search->debug = TRUE;

// Verification test.
echo "Testing Verification:\n";
if ($search->VerifyAccount($argv[1], $argv[2])) {
  echo " Account Verified.\n";
} else {
  echo " Invalid Account.\n";
  exit(1);
}

// Search request test.
echo "Testing search query for (test):\n";
$curl = curl_init();
curl_setopt_array($curl, [
  CURLOPT_RETURNTRANSFER => TRUE,
  CURLOPT_SSL_VERIFYHOST => FALSE,
  CURLOPT_SSL_VERIFYPEER => FALSE,
  CURLOPT_TIMEOUT => 20,
  CURLOPT_FOLLOWLOCATION => TRUE,
  CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP)',
]);
$search->prepare($curl, $argv[3], $argv[1], $argv[2]);
$result = curl_exec($curl);
curl_close($curl);

printf("\n %20.20s \t|\t %8.8s \t|\t %16.16s \t|\t %6.6s \t|\t %6.6s \t|\t %10.10s",
    'Title',
    'Size',
    'Time',
    'Seed',
    'Leech',
    'Source');

$plugin = new FakePlugin();
$count = $search->parse($plugin, $result);
foreach ($plugin->results as $result) {
  printf("\n%20.20s \t|\t %8.8s \t|\t %16.16s \t|\t %6.6s \t|\t %6.6s \t|\t %10.10s",
    $result->title,
    formatBytes( $result->size ),
    $result->datetime,
    $result->seeds,
    $result->seeds + $result->leechs,
    'Jacket');
}

echo "\n\nResults: {$count}";
echo "\n\n";
