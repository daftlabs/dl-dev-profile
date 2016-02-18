<?php

$projectId = $argv[1];
$apiToken = $argv[2];
$storyIds = array_slice($argv, 3);

$storyIds = array_map(function($id) {
  return trim($id, '#');
}, $storyIds);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.pivotaltracker.com/services/v5/projects/$projectId/stories?filter=id:" . implode(',', $storyIds));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "X-TrackerToken: $apiToken",
));
$response = curl_exec($ch);
curl_close($ch);

$storyData = json_decode($response);

$mask = "| %-10.10s| %-50.50s| %-30.30s | %-30s \n";
printf($mask, 'Id', 'Title', 'Labels', 'Url');
foreach ($storyData as $story) {
  $labels = array_map(function($label) {
    return $label->name;
  }, $story->labels);

  printf($mask, $story->id, $story->name, implode(', ', $labels), $story->url);
}
