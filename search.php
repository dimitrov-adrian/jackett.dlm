<?php

/**
 * Synology search class
 *
 * Stored in:   /volumeX/@appconf/DownloadStation/download/userplugins/
 * Invoker:     /volumeX/@appstore/DownloadStation/btsearch/btsearch.php
 */
class SynoDLMSearchJackett
{

  public $debug = true;
  private $qurl = 'http://<host>/api/v2.0/indexers/all/results/torznab/api?apikey=<apikey>&t=search&cat=&q=<query>';
  private $categoriesDb = [];

  public function __construct()
  {
    $this->categoriesDb = include(__DIR__ . '/categories.php');
  }

  /**
   * synology hook
   *
   * @param $curl
   * @param $query
   * @param $username
   * @param $password
   */
  public function prepare($curl, $query, $username = '', $password = '')
  {

    // Strip protocols from the setting.
    $username = strtr($username, [
      'http://' => '',
      'https://' => '',
    ]);

    $url = strtr($this->qurl, [
      '<host>' => trim($username, ' /'),
      '<apikey>' => urlencode(trim($password)),
      '<query>' => urlencode(trim($query)),
    ]);

    if ($this->debug) {
      error_log("REQUEST: {$url}");
    }

    // Setup the $curl handler.
    curl_setopt_array($curl, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_FAILONERROR => true,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_USERAGENT => DOWNLOAD_STATION_USER_AGENT,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ]);
  }

  /**
   * synology hook
   *
   * @param $plugin
   * @param $response
   *
   * @return int
   */
  public function parse($plugin, $response)
  {

    $count = 0;

    $xml = simplexml_load_string(trim($response), 'SimpleXMLElement');

    if (empty($xml->channel)) {
      return $count;
    }

    foreach ($xml->channel->item as $child) {

      $peers = $child->xpath('torznab:attr[@name="peers"]')
        ? (int) $child->xpath('torznab:attr[@name="peers"]')[0]['value']
        : 0;

      $seeds = $child->xpath('torznab:attr[@name="seeders"]')
        ? (int) $child->xpath('torznab:attr[@name="seeders"]')[0]['value']
        : 0;

      $categories = [];
      foreach ($child->xpath('torznab:attr[@name="category"]') as $category) {
        if (!empty($category['value'])) {
          $categories[] = !isset($this->categoriesDb[(int) $category['value']])
            ? 'Other'
            : $this->categoriesDb[(int) $category['value']];
        }
      }

      $indexer = (string) $child->jackettindexer;
      $leechs = $peers ? $peers - $seeds : 0;
      $title = urldecode((string) $child->title);
      $download = (string) $child->link;
      $size = (double) $child->size;
      $datetime = date('Y-m-d H:i:s', strtotime($child->pubDate));
      $page = (string) $child->guid;

      if ($indexer) {
        $title .= ' (' . $indexer . ')';
      }

      // Add record for every category
      foreach ($categories as $category) {
        $hash = md5($category . $download);
        $plugin->addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category);
        $count++;
      }
    }

    return $count;
  }

  /**
   * synology hook
   *
   * @param $username
   * @param $password
   *
   * @return bool
   */
  public function VerifyAccount($username, $password)
  {

    // Make new curl object for the verify request.
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_FAILONERROR => true,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_USERAGENT => DOWNLOAD_STATION_USER_AGENT,
    ]);

    // Use the same prepare method.
    $this->prepare($curl, '', $username, $password);

    $result = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if (strpos($result, 'Invalid API Key') !== false) {
      return false;
    }

    // Assume that if HTTP status code is 200, then we have connection to the Jackett
    return (int) $httpcode == 200;
  }

}
