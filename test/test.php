<?php

if (empty($argv[1]) && empty($argv[2])) {
  echo "\nUsage: make tests ARGS=\"<username> <password>\"\n";
  exit;
}

echo "\n";

require __DIR__ . '/../search.php';
require __DIR__ . '/FakePlugin.php';

$search = new SynoDLMSearchJackett();
$search->debug = TRUE;

// Verification test.
echo "Testing verification:\n";
echo $search->VerifyAccount($argv[1], $argv[2]) ? 'OK' : 'ERROR';
echo "\n\n";

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
$search->prepare($curl, '', $argv[1], $argv[2]);
$result = curl_exec($curl);
curl_close($curl);

printf("\n %10.10s \t|\t %8.8s \t|\t %16.16s \t|\t %6.6s \t|\t %6.6s \t|\t %10.10s",
    'Title',
    'Size',
    'Time',
    'Seed',
    'Leech',
    'Source');

$plugin = new FakePlugin();
$count = $search->parse($plugin, $result);
foreach ($plugin->results as $result) {
  printf("\n%10.10s \t|\t %8.8s \t|\t %16.16s \t|\t %6.6s \t|\t %6.6s \t|\t %10.10s",
    $result->title,
    $search->formatBytes( $result->size ),
    $result->datetime,
    $result->seeds,
    $result->seeds + $result->leechs,
    'Jacket');
}

echo "\n\nResults: {$count}";
echo "\n\n";
