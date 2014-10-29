<?php

include_once 'ParseHelper.php';

$string_literal_sq = ParseHelper::regex("/'.*?'/");
$string_literal_dq = ParseHelper::regex('/".*?"/');

$string_literal = ParseHelper::either(array($string_literal_dq, $string_literal_sq));

$whitespace = ParseHelper::whitespace();

$maybe_whitespace = ParseHelper::maybe($whitespace);


$tag = ParseHelper::regex("/\w+/");

// $attribute = ParseHelper::sequence(array($whitespace, $tag, ParseHelper::literal('='), $string_literal));
$attribute = ParseHelper::sequence_tagger(array(
	// array('parser' => $whitespace),
	array('add' => true, 'optional' => true, 'tag' => 'name', 'parser' => $tag),
	array('parser' => ParseHelper::literal('=')),
	array('add' => true, 'tag' => 'value', 'parser' => $string_literal),
));

$attributes = ParseHelper::Maybe(ParseHelper::separated_repetition($attribute, $whitespace));

// $open_tag = ParseHelper::sequence(array(ParseHelper::literal("<"), $maybe_whitespace, $tag, $attributes, ParseHelper::literal(">")));
$open_tag = ParseHelper::sequence_tagger(array(
	array('parser' => ParseHelper::literal("<")),
	array('parser' => $maybe_whitespace),
	array('add' => true, 'tag' => 'tag', 'parser' => $tag),
	array('parser' => $maybe_whitespace),
	array('add' => true, 'optional' => true, 'tag' => 'attributes', 'parser' => $attributes),
	array('parser' => ParseHelper::literal(">")),
));

// $close_tag = ParseHelper::sequence(array(ParseHelper::literal("</"), $maybe_whitespace, $tag, $maybe_whitespace, ParseHelper::literal(">")));

$close_tag = ParseHelper::sequence_tagger(array(
	array('parser' => ParseHelper::literal("</")),
	array('parser'=> $maybe_whitespace),
	array('add' => true, 'tag' => 'tag', 'parser' => $tag),
	array('parser' => $maybe_whitespace),
	array('parser' => ParseHelper::literal(">"))
));

$content = ParseHelper::literal("yeah");
$element = null;
$element_content = ParseHelper::either(array(
	// ParseHelper::regex('/.*/'), // some random string
	&$element, // recursion... fuck...
	ParseHelper::regex("/[\w\s'\"%0-9\-_\.,]+/"),
	// ParseHelper::literal("heyyy"),
	// ParseHelper::zero(),
));

$element = ParseHelper::repetition(ParseHelper::sequence_tagger(array(
	array('parser' => $maybe_whitespace),
	array('add' => true, 'tag' => 'open_tag', 'parser' => $open_tag),
	array('add' => true, 'tag' => 'content', 'parser' => $element_content),
	array('add' => true, 'tag' => 'closing_tag', 'parser' => $close_tag),
	array('parser' => $maybe_whitespace),
)));

?>
