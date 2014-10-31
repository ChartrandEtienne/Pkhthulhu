<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

include_once 'ParseHelper.php';
include_once 'meta.php';
include_once 'compiler.php';

$mock_data = '[{"id":1,"first_name":"Jeremy","last_name":"Foster","email":"jfoster0@plala.or.jp","country":"Colombia","ip_address":"61.173.239.167"},
{"id":2,"first_name":"Carl","last_name":"Graham","email":"cgraham1@paginegialle.it","country":"Indonesia","ip_address":"44.235.27.226"},
{"id":3,"first_name":"Dorothy","last_name":"Daniels","email":"ddaniels2@ifeng.com","country":"Russia","ip_address":"225.136.106.138"}]
';

$string_literal = ParseHelper::regex('/^".*?"/');

$object = null;
$array = null;

$value = ParseHelper::either_tagger(array(
	array('choice' => 'string', 'parser' => ParseHelper::regex('/^".*?"/')),
	array('choice' => 'number', 'parser' => ParseHelper::regex('/^\d+/')),
	array('choice' => 'true', 'parser' => ParseHelper::literal('true')),
	array('choice' => 'false', 'parser' => ParseHelper::literal('false')),
	array('choice' => 'null', 'parser' => ParseHelper::literal('null')),
	array('choice' => 'object', 'parser' => &$object),
	array('choice' => 'array', 'parser' => &$array),
));

$array = ParseHelper::sequence_tagger(array(
	array('parser' => ParseHelper::literal("[")),
	array('add' => true, 'tag' => 'elements', 'parser' => ParseHelper::separated_repetition($value, ParseHelper::literal(","))),
	array('parser' => ParseHelper::literal("]")),
));

$object_element = ParseHelper::sequence_tagger(array(
	array('add' => true, 'tag' => 'key', 'parser' => ParseHelper::regex('/^".*?"/')),
	array('parser' => ParseHelper::literal(":")),
	array('add' => true, 'tag' => 'value', 'parser' => &$value),
));

$object = ParseHelper::sequence_tagger(array(
	array('parser' => ParseHelper::literal("{")),
	array('add' => true, 'tag' => 'elements', 'parser' => ParseHelper::separated_repetition($object_element, ParseHelper::literal(","))),
	array('parser' => ParseHelper::literal("}")),
));


$mock_data = '["it",12,{"works":["gr",8],"see":"that","shit":{"m":8}},1337]';
// $mock_data = '[12]';
// $mock_data = '12';

// $test = ParseHelper::regex('/^".*?"/');
// $test = ParseHelper::regex('/^\d+?/');

echo "<pre>";
echo "\ndatas: \n";
echo $mock_data . "\n";
print_r(json_encode($value($mock_data)));


echo '</pre>';
?>
