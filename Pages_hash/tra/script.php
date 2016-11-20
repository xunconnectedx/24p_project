<?php
$file1="Difference/a";
$file2="Difference/b";
Differences($file1,$file2);
function Differences($file1,$file2)
{
//html префикс
$fear1='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
		<title>CHECK DIFFERENCES</title>
		<link rel="stylesheet" href="../styles.css" type="text/css" charset="utf-8"/>
	</head>
	<body>
		<h1>DIFFERENCES</h1>
		<hr />';
//html постфикс
$fear2 = '		</pre>
	</body>
</html>';
$sitefolder='sitename';//передавать эту переменную скрипту
require_once dirname(__FILE__).'/Difference/Diff.php';
$a = explode("\n", file_get_contents(dirname(__FILE__).'/'.$file1));
		$b = explode("\n", file_get_contents(dirname(__FILE__).'/'.$file2));


$options = array(
			//'ignoreWhitespace' => true,
			//'ignoreCase' => true,
		);

		// Initialize the diff class
		$diff = new Diff($a, $b, $options);

		// Generate a side by side diff
		require_once dirname(__FILE__).'/Difference/Diff/Renderer/Html/SideBySide.php';
		$renderer = new Diff_Renderer_Html_SideBySide;
		$resp=$diff->Render($renderer);
		$out=$fear1 . $resp . $fear2;
		 if (!is_dir('diffs/'.$sitefolder)) mkdir('diffs/'.$sitefolder, 0777); //папка для файлов
		file_put_contents('diffs/'.$sitefolder.'/test.html', $out);
//FILE_APPEND
}
?>