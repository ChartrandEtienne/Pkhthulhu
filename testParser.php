<?php

include_once 'ParseHelper.php';

echo "ohey!\n ";
// var_dump(substr('abc', 0, 1));
$haystack = "foobarbaz";
$needle = "foo";
// var_dump(substr($haystack,  strlen($needle)));
$test1 = ParseHelper::literal("foobarbaz");
echo "<p>literal</p><p>";
echo json_encode($test1("foobarbaz"));
$test2 = ParseHelper::literal("barbazfish");
$data1 = array($test1, $test2);
$test3 = ParseHelper::either($data1);
echo "</p><p>either</p><p>";
echo json_encode($test3("barbazfish"));

$str1 = "barbazfishfoobarbaz";
$data3 = array($test2, $test1);
$test4 = ParseHelper::sequence($data3);
echo "</p><p>sequence</p><p>";
echo htmlspecialchars(json_encode($test4($str1)));

$res = preg_match("/\w+/", "b212----", $matches);
echo "</p><p>matches</p><p>";
echo htmlspecialchars(json_encode($matches));
echo "</p><p>res</p><p>";
echo htmlspecialchars(json_encode($res));
$test5 = ParseHelper::regex('/\w+/');
echo "</p><p>regex</p><p>";
echo htmlspecialchars(json_encode($test5("ogowogo")));


$res = preg_match("/'.*?'/", "data='banane' params='poire'", $matches);
$res = preg_match("/\w+/", "data='banane'", $matches);
echo "</p><p>string literals</p><p>";
echo htmlspecialchars(json_encode($matches));

// $test6 = ParseHelper::regex("/\s+/");
$test6 = ParseHelper::whitespace();
echo "</p><p>whitespace</p><p>";
echo htmlspecialchars(json_encode($test6("	\n ")));

// $test7 = ParseHelper::maybe($string_literal_sq);
// echo "</p><p>maybe</p><p>";
// echo htmlspecialchars(json_encode($test7("'blabla'")));

$test8 = ParseHelper::repetition(ParseHelper::literal("yeah"));
echo "</p><p>sequence</p><p>";
echo htmlspecialchars(json_encode($test8("yeahyeahyeah")));

include 'xmlParser.php';

$mild = <<< EOT
<div id="hero-content">
        <span id="controls">
            <a href="/tour" id="tell-me-more" class="button">Take the 2-minute tour</a>
            <span id="close"><a title="click to dismiss">tsss</a></span>
        </span>
        <div id="blurb">
            sTACK oVErflow is a question and answer site for professional and enthusiast programmers. It's 100% free, no registration required.
        </div>
    </div>
EOT;

// $mild = <<< EOT
// <div><boz>heyy</boz></div>
// EOT;

$wild = <<< EOT
<div id="hero-content">
        <span>
            <a href="/tour" id="tell-me-more" class="button">Take the 2-minute tour</a>
            <span id="close"><a title="click to dismiss">tsss</a></span>
        </span>
        <div id="blurb">
            sTACK oVErflow is a question and answer site for professional and enthusiast programmers. It's 100% free, no registration required.
        </div>
    </div>
EOT;

$wild = <<<EOT
<div>
	<div id="contained-1">
	</div>
	<div id="contained-2">
	</div>
	<div id="contained-3">
	</div>
	<div id="contained-4">
	</div>
	<div id="contained-5">
	</div>
	<div id="contained-6">
	</div>
</div>
EOT;

$mild = <<<EOT
lel
EOT;


// $parse_html = ParseHelper::sequence(array($open_tag, $content, $close_tag));
//
// $xml= "<hella>yeah</hella>";
//
// echo "</p><p>html</p><p>";
// echo htmlspecialchars(json_encode($parse_html($xml)));


echo "</p><p>html better</p><p>";
echo htmlspecialchars(json_encode($element($wild)));
echo '</p>';
echo "<div id='tutu'class='toto'>worky</div>";

?>
