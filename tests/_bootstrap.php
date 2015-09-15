<?php

$console = new \Codeception\Lib\Console\Output([]);

$console->writeln('Loading WP core...');
require_once(getenv('WP_TEST_PATH') . '/wp-load.php');

$console->writeln('Cleaning up database...');
$models = array(
  'Subscriber',
  'Setting',
  'Newsletter',
  'Segment',
  'SubscriberSegment'
);
$destroy = function ($model) {
  Model::factory('\MailPoet\Models\\' . $model)
    ->deleteMany();
};
array_map($destroy, $models);
