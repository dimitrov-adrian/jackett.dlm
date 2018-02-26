<?php

require __DIR__ . '/FakeResult.php';

/**
 * Fake Plugin class
 */
final class FakePlugin
{

  public $results = [];

  public function __construct()
  {

  }

  /**
   * Add result method
   *
   * @param $title
   * @param $size
   * @param $datetime
   * @param $page
   * @param $hash
   * @param $seeds
   * @param $leechs
   * @param $category
   */
  public function addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category)
  {
    $result = new FakeResult;
    $result->title = $title;
    $result->download = $download;
    $result->size = $size;
    $result->datetime = $datetime;
    $result->page = $page;
    $result->hash = $hash;
    $result->seeds = $seeds;
    $result->leechs = $leechs;
    $result->category = $category;

    array_push($this->results, $result);
  }

}
