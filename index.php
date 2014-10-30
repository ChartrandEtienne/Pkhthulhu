<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

include_once 'ParseHelper.php';
include_once 'meta.php';
include_once 'compiler.php';

echo '<pre>';





// $weeds_format = '[{name:RE\w+GEX,price:RE\d+GEX,qty:RE\d+GEX},"\n"]';
$weeds_format = '[{RE\w+GEX,price:RE\d+GEX,qty:RE\d+GEX},"\n"]';
// $weeds_format = '[{name:RE\w+GEX},","]';
// $weeds_format = '[{yup:"eh"},","]';

$ok = array(
	// 'ident' => 'ident',
	'RE\w+GEX' => 'blabla',
	'RE,GEX' => ',',
	'@variable' => '@variable',
	'"literal"' => '"literal"',
	'["sep_list",RE,GEX]' => '"sep_list","sep_list"',
	'{ident:"literal"}' => '{ident:"literal"}',
);

foreach($ok as $o => $test) {
	echo "\npattern: " . $o  . "\n";
	$result = $pattern($o)['result'];
	print_r($result);
	$compiled = $compile_dispatcher($result);
	// echo "\ndispatcher: \n";

	// print_r($compiled);
	if ("error" != $compiled) {
		$parsed = $compiled($test);
		echo "\ncompiled:\n";
		print_r($parsed);
	}

	/*
	if (isset($result['choice'])) {
		if (isset($compile_dispatcher[$result['choice']])) {
			$compiler = $compile_dispatcher[$result['choice']];
			$compiled = $compiler($result);
			$parsed = $compiled($test);
			echo "\ncompiled:\n";
			print_r($parsed);
		}
	}
	*/
}


// echo htmlspecialchars(json_encode($pattern($weeds_format)));
// echo htmlspecialchars(json_encode($meta_sequence_members($weeds_format)));
// print_r($meta_sequence_members($weeds_format));
print_r($pattern($weeds_format));


$weed_format = <<<EOT
@name=RE\w+GEX;@price=RE\d+GEX;@qty=RE\d+GEX;@new="\n";
[{name:@name,price:@price,qty:@qty},@new}]
EOT;

$xml_format = <<<EOT
@space=RE\s+GEX;
@name=RE\w+GEX;
@string_literal=({"\"",value:RE\w+GEX,"\""}|{"'",value:RE\w+GEX,"'"});
@attribute={name:@name,?space,"=",?space,value:@string_literal};
@attributes=[@attributes,@space];
@open_tag={"<",space?,tag:@name,?attributes:@attributes,?space,">"};
@close_tag={"</",?space,@name,?space,">"};
EOT;

$weed_parser = ParseHelper::separated_repetition(ParseHelper::sequence_tagger(array(
	array('add' => true, 'tag' => 'name', 'parser' => ParseHelper::regex('/\w+/')),
	array('parser' => ParseHelper::literal('|')),
	array('add' => true, 'tag' => 'price', 'parser' => ParseHelper::regex('/\d+/')),
	array('parser' => ParseHelper::literal('|')),
	array('add' => true, 'tag' => 'qty', 'parser' => ParseHelper::regex('/\d+/')),
)), ParseHelper::literal("\n"));

// echo "</p><p>deal</p><p>";
// echo htmlspecialchars(json_encode($weed_parser($mild)));

echo '</pre>';

?>
