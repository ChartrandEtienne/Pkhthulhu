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
