<?php

namespace app\commands;
use app\commands\diff\lib\Diff;
use app\commands\diff\lib\Diff\Renderer\Html\SideBySide;
use yii;
use yii\console\Controller;
use app\models\Urls;
use app\models\Objects;

class RevizDiffController extends Controller
{
    public function actionIndex(/*$file1, $file2*/) //первая запись в базу (корень)
    {
		
       /*<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
        <title>PHP LibDiff - Examples</title>
        <link rel="stylesheet" href="/diff/lib/styles.css" type="text/css" charset="utf-8"/>
    </head>
    <body>
        <h1>PH123123P LibDiff - Examples</h1>
        <hr />
        <?php*/

        // Include the diff class
        //require_once dirname(__FILE__).'/diff/lib/Diff.php';

        // Include two sample files for comparison
        $a = explode("\n", file_get_contents(dirname(__FILE__).'/../1'));//имена файлов
        $b = explode("\n", file_get_contents(dirname(__FILE__).'/../2'));//имена файлов

        // Options for generating the diff
        $options = array(
            //'ignoreWhitespace' => true,
            //'ignoreCase' => true,
        );

        // Initialize the diff class
        $diff = new Diff($a, $b, $options);

        /*?>
        <h2>Side by Side Diff</h2>
        <?php*/

        // Generate a side by side diff
        require_once dirname(__FILE__).'/diff/lib/Diff/Renderer/Html/SideBySide.php';
        $renderer = new Diff_Renderer_Html_SideBySide;
        echo $diff->Render($renderer);

        /*?>
        <h2>Inline Diff</h2>
        <?php*/

        /*// Generate an inline diff
        require_once dirname(__FILE__).'/diff/lib/Diff/Renderer/Html/Inline.php';
        $renderer = new Diff_Renderer_Html_Inline;
        echo $diff->render($renderer);

        ?>
        <h2>Unified Diff</h2>
        <pre><?php

        // Generate a unified diff
        require_once dirname(__FILE__).'/diff/lib/Diff/Renderer/Text/Unified.php';
        $renderer = new Diff_Renderer_Text_Unified;
        echo htmlspecialchars($diff->render($renderer));

        ?>
        </pre>
        <h2>Context Diff</h2>
        <pre><?php

        // Generate a context diff
        require_once dirname(__FILE__).'/diff/lib/Diff/Renderer/Text/Context.php';
        $renderer = new Diff_Renderer_Text_Context;
        echo htmlspecialchars($diff->render($renderer));
        /*?>
        </pre>
    </body>*/

    }

    public function actionCheckDiff() //проверка уже существующих ссылок
    {
        $izm[]='';
        //последовательное чтение всех ссылок из базы с расчетом и сравнением хэша
        /*ПОИСК В ССЫЛКАХ*/
        $find = Urls::find()->all();
        foreach($find as $value)
        {
            //echo $value->url . "\n";
            $client = new Client();
            $response = $client->createRequest()->setUrl($value->url)
            ->setOptions(['userAgent'=> 'Googlebot'])->send();
            if($response->isOk)
            {
                if ($value->hash!=$this->hash_test($response->content))
                {

                    //echo $value->hash . '<>' . $this->hash_test($response->content) . "!!!\n";
                    array_push($izm, $value->url);
                }
            } else echo "Что-то пошло не так! (проблема соединения)\n";
        }
        /*ПОИСК В ОБЪЕКТАХ*/
        $find = Objects::find()->all();
        foreach($find as $value)
        {
            //echo $value->url . "\n";
            $client = new Client();
            $response = $client->createRequest()->setUrl($value->url)
            ->setOptions(['userAgent'=> 'Googlebot'])->send();
            if($response->isOk)
            {
                if ($value->hash!=$this->hash_test($response->content))
                {

                    //echo $value->hash . '<>' . $this->hash_test($response->content) . "!!!\n";
                    array_push($izm, $value->url);
                }
            } else echo "Что-то пошло не так! (проблема соединения)\n";
        }
        var_dump($izm);
    }

	function hash_test($content) //принимает переменную с данными и выдает хэш
	{
		file_put_contents("File.html",$content);
		return hash_file('sha1', 'File.html');
		//unlink("File.html");
	}   
}