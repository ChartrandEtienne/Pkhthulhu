<?php

// so let's see
// identifier: [\w_]+
// variable: @[\w_]+
// literal: "literal"
// either: (patter1|pattern2|pattern3)
// tagged_sequence: {tag1:pattern1,tag2:pattern2,?optional:pattern,anon_pattern,tag3:pattern3,?anon_optional}
// tagged_seq_mem: (anon_pattern|?optional_anon|tag:pattern|?name:optional_pattern)
// tagged_sequence: {[@tagged_seq_mem,","]}
// maybe: pattern? // really it's possible that this only makes sense in the tagged_sequence context
// repetition: [pattern]
// separated_repetition: [pattern,separator]
// regex: REpatternGEX
// I actually need to fucking name my patterns
// $var=pattern;
// ...
// weeds

include_once 'ParseHelper.php';

$meta_identifier = ParseHelper::regex('/^[\w_]+/');
$meta_variable_name = ParseHelper::regex('/^@[\w_]+/');
$meta_literal = ParseHelper::regex('/^".*?"/');
$meta_regex = ParseHelper::regex('/^RE.*?GEX/');

$pattern = null; // recursion

$meta_either = ParseHelper::sequence_tagger(array(
	array('parser' => ParseHelper::literal('(')),
	array('add' => true, 'tag' => 'choices', 'parser' => ParseHelper::separated_repetition($pattern, ParseHelper::literal("|"))),
	array('parser' => ParseHelper::literal(')')),
));

$meta_sequence_members = ParseHelper::either_tagger(array(
	// anonymous pattern alone
	array('choice' => 'anon', 'parser' => ParseHelper::sequence_tagger(array(
		array('add' => true, 'tag' => 'pattern', 'parser' => &$pattern),
	))),

	array('choice' => 'pair', 'parser' => ParseHelper::sequence_tagger(array(
		array('add' => true, 'tag' => 'identifier', 'parser' => $meta_identifier),
		array('parser' => ParseHelper::literal(":")),
		array('add' => true, 'tag' => 'pattern', 'parser' => &$pattern),
	))),
	// option anon pattern alone
	array('choice' => 'option_anon', 'parser' => ParseHelper::sequence_tagger(array(
		array('parser' => ParseHelper::literal("?")),
		array('add' => true, 'tag' => 'pattern', 'parser' => &$pattern),
	))),
	// tag:pattern pair
	array('choice' => 'pair', 'parser' => ParseHelper::sequence_tagger(array(
		array('add' => true, 'tag' => 'identifier', 'parser' => $meta_identifier),
		array('parser' => ParseHelper::literal(":")),
		array('add' => true, 'tag' => 'pattern', 'parser' => &$pattern),
	))),
	// ?tag:pattern optional pair
	array('choice' => 'option_pair', 'parser' => ParseHelper::sequence_tagger(array(
		array('parser' => ParseHelper::literal("?")),
		array('add' => true, 'tag' => 'identifier', 'parser' => $meta_identifier),
		array('parser' => ParseHelper::literal(":")),
		array('add' => true, 'tag' => 'pattern', 'parser' => &$pattern),
	))),
));

$meta_tagged_sequence = ParseHelper::sequence_tagger(array(
	array('parser' => ParseHelper::literal("{")),
	array('add' => true, 'tag' => 'elements', 'parser' => ParseHelper::separated_repetition($meta_sequence_members, ParseHelper::literal(","))),
	array('parser' => ParseHelper::literal("}")),
));

$meta_separated_repetition = ParseHelper::sequence_tagger(array(
	array('parser' => ParseHelper::literal("[")),
	array('add' => true, 'tag' => 'elements', 'parser' => &$pattern),
	array('parser' => ParseHelper::literal(",")),
	array('add' => true, 'tag' => 'separator', 'parser' => &$pattern),
	array('parser' => ParseHelper::literal("]")),
));

$pattern = ParseHelper::either_tagger(array(
	array('choice' => 'meta_variable_name', 'parser' => $meta_variable_name),
	array('choice' => 'meta_literal', 'parser' => $meta_literal),
	array('choice' => 'meta_regex', 'parser' => $meta_regex),
	array('choice' => 'meta_either', 'parser' => $meta_either),
	array('choice' => 'meta_tagged_sequence', 'parser' => $meta_tagged_sequence),
	array('choice' => 'meta_separated_repetition', 'parser' => $meta_separated_repetition),
));

?>
