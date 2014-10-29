<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

include_once 'ParseHelper.php';
include_once 'meta.php';

echo '<pre>';





// $weeds_format = '[{name:RE\w+GEX,price:RE\d+GEX,qty:RE\d+GEX},"\n"]';
$weeds_format = '[{RE\w+GEX,price:RE\d+GEX,qty:RE\d+GEX},"\n"]';
// $weeds_format = '[{name:RE\w+GEX},","]';
// $weeds_format = '[{yup:"eh"},","]';

$ok = array('ident', 'RE\w+GEX', '@variable', '"literal"',
	'["sep_list",","]',
	'{ident:"literal"}',
);

foreach($ok as $o) {
	echo "\nforeach\n";
	print_r($pattern($o));
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
