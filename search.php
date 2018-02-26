<?php

/**
 * Synology search class
 */
class SynoDLMSearchJackett
{

  public $debug = FALSE;
  private $qurl = 'http://<host>/api/v2.0/indexers/all/results/torznab/api?apikey=<apikey>&t=search&cat=&q=<query>';

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
    curl_setopt($curl, CURLOPT_URL, $url);
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
        ? (int)$child->xpath('torznab:attr[@name="peers"]')[0]['value']
        : 0;

      $seeders = $child->xpath('torznab:attr[@name="seeders"]')
        ? (int)$child->xpath('torznab:attr[@name="seeders"]')[0]['value']
        : 0;

      $categories = [];
      foreach ($child->xpath('torznab:attr[@name="category"]') as $category) {
        $categories[] = $category['value'];
      }

      $leechs = $peers ? $peers - $seeders : 0;
      $title = (string) $child->title;
      $download = (string) $child->link;
      $size = $this->formatBytes( (double) $child->size );
      $size = (double) $child->size;
      $datetime = date('Y-m-d H:i:s', strtotime($child->pubDate));
      $page = (string) $child->guid;
      $hash = '';

      // Add record for every category
      // foreach ($categories as $category) {
      //   $plugin->addResult($title, $download, $size, $datetime, $page, $hash, $seeders, $leechs, $category);
      // }

      $plugin->addResult($title, $download, $size, $datetime, $page, $hash, $seeders, $leechs, array_shift($categories));

      $count++;
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
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_SSL_VERIFYHOST => FALSE,
      CURLOPT_SSL_VERIFYPEER => FALSE,
      CURLOPT_TIMEOUT => 20,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP)',
    ]);

    // Use the same prepare method.
    $this->prepare($curl, '', $username, $password);

    $result = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    // Assume that if HTTP status code is 200, then we have connection to the Jackett
    return (int) $httpcode == 200;
  }

}
