<?php

include_once 'ParseHelper.php';

$compile_dispatcher = null;

$compile_function = array(
	'meta_regex' => function($to_compile) {
		$pattern = $to_compile['pattern'];
		preg_match("/RE(.+)GEX/", $pattern, $matches);
		$regex = $matches[1];
		return ParseHelper::regex("/^" . $regex . "/");
	},

	'meta_variable_name' => function($to_compile) {
		$literal = $to_compile['pattern'];
		return ParseHelper::literal($literal);
	},

	'meta_literal' => function($to_compile) {
		$literal = $to_compile['pattern'];
		return ParseHelper::literal($literal);
	},

	'meta_separated_repetition' => function($to_compile) use (&$compile_dispatcher) {
		$elements = $compile_dispatcher($to_compile['elements']);
		$separator = $compile_dispatcher($to_compile['separator']);
		$to_return = ParseHelper::separated_repetition($elements, $separator);
		return $to_return;
	},

	'meta_tagged_sequence' => function($to_compile) use (&$compile_dispatcher) {
		$elements = array();
		foreach ($to_compile['elements'] as $to) {
			switch($to['choice']) {
				case "pair":
					$parser = $compile_dispatcher($to['pattern']);
					$identifier = $to['identifier']['pattern'];
					$identifier_parser = ParseHelper::literal($identifier);
					$elements[] = array('parser' => $identifier_parser);
					$elements[] = array('add' => true, 'tag' => $identifier, 'parser' => $parser);
				break;

				case "option_pair":
					// ugly and annoying
					$parser = $compile_dispatcher($to['pattern']);
					$identifier = $to['identifier']['pattern'];
					$identifier_parser = ParseHelper::literal($identifier);
					$elements[] = array('add' => true, 'tag' => $identifier, 'parser' => ParseHelper::maybe(ParseHelper::sequence_tagger(array(
						array('parser' => $identifier_parser),
						array('add' => true, 'tag' => $identifier, 'parser' => $parser),
					))));
				break;
				case "anon":
					$parser = $compile_dispatcher($to['pattern']);
					// add or no add?
					$elements[] = array('add' => true, 'parser' => $parser);
				break;

				case "option_anon":
					$parser = ParseHelper::maybe($compile_dispatcher($to['pattern']));
					// add or no add?
					$elements[] = array('add' => true, 'parser' => $parser);
				break;
				default:
					echo "\nFUCKUP\n";
				break;
			}
		}
		$to_return = ParseHelper::sequence_tagger($elements);
		return $to_return;
	},
	// either

);

$compile_dispatcher = function($to_compile) use ($compile_function) {
	if (isset($to_compile['choice']) && isset($compile_function[$to_compile['choice']])) {

		$compiler = $compile_function[$to_compile['choice']];
		$compiled = $compiler($to_compile);
		return $compiled;
	} else {
		return "error";
	}
};

?>
