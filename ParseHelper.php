<?php

class ParseHelper {
	// returns array(Date, string) or error


	public static function result($result) {
		return function($input) use ($result) {
			return array('result' => $result, 'next' => $input, 'success' => true);
		};
	}

	public static function zero() {
		return function($input) {
			return array('success' => false);
		};
	}

	public static function item() {
		return function($input) {
			if (strlen($input) > 0) {
				$rest = substr($input, 1);
				$rest = $rest ? $rest : '';
				$res = substr($input, 0, 1);
				return array('result' => $res, 'next' => $rest, 'success' => true);
			} else {
				return array('success' => false);
			}
		};
	}

	public static function literal($literal) {
		return function($input) use ($literal) {
			if (0 === strpos($input, $literal)) {
				$res = substr($input, 0, strlen($literal));
				$next = substr($input, strlen($literal));
				return array('result' => $res, 'next' => $next, 'success' => true);
			} else {
				return array('success' => false);
			}
		};
	}

	public static function either_tagger($choices) {
		return function($input) use ($choices) {
			foreach ($choices as $choice) {
				$res = $choice['parser']($input);
				if ($res['success']) {
					$res['result']['choice'] = $choice['choice'];
					return $res;
				}
			}
			return array('success' => false);
		};
	}

	public static function either($choices) {
		return function($input) use ($choices) {

			// echo "<br>choices<br>";
			// var_dump($input);
			foreach ($choices as $choice) {
				$res = $choice($input);
				if ($res['success']) {
					return $res;
				}
			}
			return array('success' => false);
		};
	}

	public static function sequence_tagger($sequence) {
		return function($input) use ($sequence) {

			// echo "<br>sequence<br>";
			// var_dump($input);
			$results = array();
			foreach ($sequence as $seq) {
				$parser = $seq['parser'];
				$optional = isset($seq['optional']) && $seq['optional'];
				$add = isset($seq['add']) && $seq['add'];
				$tag = isset($seq['tag']) ? $seq['tag'] : false;
				$res = $seq['parser']($input);
				$failed_maybe = isset($res['maybe']) && $res['maybe'];
				if ($res['success']) {
					if ($add) {
						if (!($failed_maybe && $optional)) {
							if ($tag) {
								// $res['tag'] = $tag;
								$results[$tag] = $res['result'];
								// $results[] = $res['result'];
							} else {
								$results[] = $res['result'];
							}
						}
					}
					$input = $res['next'];
				} else {
					return $res;
				}
			}
			return array('result' => $results, 'next' => $input, 'success' => true);
		};
	}

	public static function sequence($sequence) {
		return function($input) use ($sequence) {

			// echo "<br>sequence<br>";
			// var_dump($input);
			$results = array();
			foreach ($sequence as $seq) {
				$res = $seq($input);
				if ($res['success']) {
					$results[] = $res['result'];
					$input = $res['next'];
				} else {
					return $res;
				}
			}
			return array('result' => $results, 'next' => $input, 'success' => true);
		};
	}

	public static function separated_repetition($pattern, $separator) {
		return function($input) use ($pattern, $separator) {
			$results = array();
			while (true) {
				// echo "<br>REPETITION<br>";
				// echo $input;
				// var_dump($results);
				$res = $pattern($input);
				if ($res['success']) {
					$results[] = $res['result'];
					$input = $res['next'];
				} else {
					if (0 == count($results)) {
						return array('success' => false);
					}
					break;
				}

				$res = $separator($input);
				if ($res['success']) {
					// $results[] = $res['result'];
					$input = $res['next'];
				} else {
					if (0 == count($results)) {
						return array('success' => false);
					}
					break;
				}
			}

			return array('result' => $results, 'next' => $input, 'success' => true);
		};
	}

	public static function repetition($pattern) {
		return function($input) use ($pattern) {
			$results = array();
			while (true) {
				// echo "<br>REPETITION<br>";
				// echo $input;
				// var_dump($results);
				$res = $pattern($input);
				if ($res['success']) {
					$results[] = $res['result'];
					$input = $res['next'];
				} else {
					if (0 == count($results)) {
						return array('success' => false);
					}
					break;
				}
			}

			return array('result' => $results, 'next' => $input, 'success' => true);
		};
	}


	public static function maybe($maybe) {
		return function($input) use ($maybe) {

			// echo "<br>maybe<br>";
			// var_dump($input);
			$res = $maybe($input);
			if ($res['success']) {
				// echo "TRUE";
				$result = $res['result'];
				$input = $res['next'];
				return array('result' => $result, 'next' => $input, 'success' => true);
			} else {
				return array('result' => true, 'next' => $input, 'success' => true, 'maybe' => true);
			}
		};
	}

	// regex are pretty wild tho
	// will behave decently if given a pattern without groupings
	// or other such wild features
	public static function regex($pattern) {
		return function($input) use ($pattern) {
			$flag = preg_match($pattern, $input, $matches, 0, 0);
			if ($flag) {
				$match = $matches[0];
				$res = array('pattern' => substr($input, 0, strlen($match)));
				$next = substr($input, strlen($match));
				return array('result' => $res, 'next' => $next, 'success' => true);
			} else {
				return array('success' => false);
			}
		};
	}

	public static function whitespace() {
		return ParseHelper::regex('/^\s+/');
	}

	public static function dateParser() {
		return function ($input) {
			$first_try = DateTime::createFromFormat('Y/m/d H:i:s', $input);
			// I just want the index of where it's not a date anymore
			if (!$first_try) {
				$end_index = array_keys(DateTime::getLastErrors()['errors'])[0];
				$second_try = DateTime::createFromFormat('Y/m/d H:i:s', substr($input, 0, $end_index));
				$next = substr($input, $end_index);
				if ($second_try) {
					return array('result' => $second_try, 'next' => $next);
				}
				else {
					return array('error' => 'dateParser error at: ' . $input);
				}
			}
			return array('result' => $first_try, 'next' => '');
		};
	}

	// just get rid of the [profile] [accesses] part, returns a token like
	// array("dump"), whatever
	public static function dumpSugarParser() {
		return function ($input) {
			if (preg_match('/\[(profile|info)\] \[accesses\]/', $input, $match)) {
				return array('result' => '[profile] [accesses]', 'next' => substr($input, strlen($match[0])));
			}
			else {
				return array('error' => 'dumpSugarParser error at: ' . $input);
			}
		};
	}

	// returns either one or two ids
	// duh
	public static function idParser() {
		return function ($input) {
			if (preg_match('/(\d\d\d\d) - (\d\d\d\d)/', $input, $matches)) {
				$rest = substr($input, 11);
				$ids = array($matches[1], $matches[2]);
				return array('result' => $ids, 'next' => $rest);
			}
			else if (preg_match('/(\d\d\d\d)/', $input, $matches)) {
				$rest = substr($input, 4);
				$ids = array($matches[1]);
				return array('result' => $ids, 'next' => $rest);
			}
			else {
				return array('error' => 'idParser error at: ' . $input);
			}

		};
	}

	// returns the url
	public static function urlParser() {
		return function ($input) {
			// there are no spaces in urls.
			$pattern = '/\/.+?\ /';

			if (preg_match($pattern, $input, $matches)) {
				$url = $matches[0];
				// echo '<pre>';
				// print_r($matches);
				// echo '</pre>';
				$rest = substr($input, strlen($url));
				return array('result' => $url, 'next' => $rest);
			}
			else {
				return array('error' => 'urlParser error at: ' . $input);
			}
			return $input;
		};
	}

	// returns the json dump
	public static function jsonParser() {
		return function ($input) {
			$res = json_decode($input, true);
			if (null == $res) {
				return array('error' => 'jsonParser error at: ' . $input);
			}
			else {
				return array('result' => $res, 'next' => '');
			}
		};
	}

	// this guy totally suck
	// I mean, it's just gonna return the head of string
	// until EOS or \n
	// because what I will probably deal with is like Array ( ) Array ( ) \n1970 bla bla
	// we'l happily deal with the Array payload later
	public static function printrParser() {
		return function ($input) {
			if (preg_match('/(\n|$)/', $input, $matches, PREG_OFFSET_CAPTURE)) {
				$result = substr($input, 0, $matches[0][1]);
				$next = substr($input, $matches[0][1]);
				return array('result' => $result, 'next' => $next);
			}
			else {
				return array('error' => 'printrParser error at: ' . $input);
			}
		};
	}

	public static function combineRepetition($parser) {
		return function ($input) use ($parser) {

				file_put_contents(Yii::app()->basePath . "/runtime/lolwut", "lel", FILE_APPEND);
			$result = array();
	 		$current_input = trim($input);
			while ($current_input) {
				$to_parse = substr($current_input, 0, 10000);

				file_put_contents(Yii::app()->basePath . "/runtime/lolwut", "\n\nto_parse:\n" . $to_parse . "\n\n", FILE_APPEND);
				// $res = $parser($current_input);
				$res = $parser($to_parse);
	 			if (isset($res['error'])) {
	 				var_dump($res);
	 				echo '<pre>';
	 				print_r("\n");
	 				echo '</pre>';
	 				die();
	 			} else {
					$result[] = $res['result'];
				}

				$next = strlen($to_parse) - strlen($res['next']);

				// file_put_contents(Yii::app()->basePath . "/runtime/lolwut", "\n\nnext:\n" . $res['next'] . "\n\nnext " . $next ."\n" , FILE_APPEND);
				$current_input = substr($current_input, $next);

				$todump = json_encode($res['result']);
				// file_put_contents(Yii::app()->basePath . "/runtime/lolwut", $todump . "\n\n", FILE_APPEND);
				// $current_input = $res['next'];
			}
			return array('result' => $result, 'next' => $current_input);
		};
	}

	public static function combineSequence($parsers) {
		return function($input) use ($parsers) {
			$result = array();
	 		$current_input = trim($input);
	 		foreach ($parsers as $parser) {
				// $to_parse = substr($current_input, 0, 1024);
	 			$res = $parser($current_input);
	 			// var_dump($res);
	 			// echo '<pre>';
	    	 	// print_r("\n");
	 			// echo '</pre>';
	 			if (isset($res['error'])) {
	 				var_dump($res);
	 				echo '<pre>here';
					var_dump($input);
	 				print_r("\n");
					print_r($res);
	 				echo '</pre>';
					return array('error' => 'combineSequence error at: ' . $input);
	 				// die();
	 			} else {
					$result[] = $res['result'];
				}
				$current_input = trim($res['next']);
			}
			return array('result' => $result, 'next' => $current_input);
		};
	}
}

