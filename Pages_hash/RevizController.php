<?php

namespace app\commands;
use yii;
use app\commands\Difference;
use yii\web\Request;
use yii\console\Controller;
use app\models\Urls;
use app\models\Objects;
use yii\httpclient\Client;
//Yii::$classMap['Diff']='home\d1van\basicYii\commands\Difference\Diff.php';
class RevizController extends Controller
{   
    public static $rooturl='http://kristall-kino.ru';
    public function actionIndex(/*$url*/) //первая запись в базу (корень)
    {
		//$url = 'http://kristall-kino.ru';
        $client = new Client();
        $response = $client->createRequest()
        	->setUrl(self::$rooturl)->setOptions(['userAgent'=> 'Googlebot', 'proxy' => 'tcp://124.88.67.63:80'])
        	->send();
        if($response->isOk)
        {
            //mkdir(substr(self::$rooturl,7), 0700);
            //file_put_contents(substr(self::$rooturl,7) . '/' . substr(self::$rooturl,7),$response->content);
        	$modelUrls = new Urls;	
        	$modelUrls->hash = $this->hash_test($response->content);
			$modelUrls->url = self::$rooturl;
            //echo $modelUrls->url;
            if (!is_dir('site')) mkdir('site', 0777);
            file_put_contents("site/" . $this->hash_test(self::$rooturl),$response->content);///
			$modelUrls->parsed = 0;
			//$modelUrls->urlhash = $this->hash_test(self::$rooturl);
            $modelUrls->ping = $this->pingsite(substr(self::$rooturl,7));//пинг
			$modelUrls->save();
        }
        else echo "Проблема соединения!\n";
    }
    
	public function actionPars() //парсинг из базы (по параметру parsed=0)
	{
		//$rooturl='http://kristall-kino.ru';
		global $countDown;
		global $countObject;
        $GLOBALS['avail']=array(); // массив для недоступных юр
        $Pars = Urls::find()->where(['parsed'=> 0])->all(); //выбираем не спарсенные
    	foreach($Pars as $value)
    	{
    		$oldCountDown = $countDown;
        	$this->Parsing($value['url']); //вызов функции поиска ссылок на странице
        	$modelUrls = Urls::find()//находим рабочий элемент и выставляем парс=1
    		->where(['url' => $value['url']])
    		->one();
			$modelUrls->parsed = 1; //указываем, что ссылка уже парсилась
    		$modelUrls->save();
        	echo "проработал ".$value['url']."\n";
        	if($countDown>$oldCountDown) 
			    {
                    $countd=$countDown-$oldCountDown;
			    	echo "В базу выгружено ".$countd." ссылок.\n";
			    	break;
			    } else {echo "Новые ссылки не найдены.\n";}
    	}
        $endPars = Urls::find()->where(['parsed' => 0])->count();
        if ($endPars>0) //если остались не спарсенные ссылки, то продолжаем
        {
            $this->actionPars(); //вызов рекурсивный    
        } else 
            {
                $find = Urls::find()->all(); /////////////////////
                foreach($find as $value)     //Очищаем поле парс//
                {                            //во всех элементах//
                    $value->parsed = 0;      //базы             //
                    $value->save();          //                 //
                }                            /////////////////////
                echo "Всего добавленно ".$countDown." ссылок и ".$countObject." объектов.\n"; //вывод кол-ва обр. объектов
                echo "Недоступные страницы/файлы:\n";/////////////////////
                foreach($avail as $value)            //Вывод недоступных//
                {                                    //страниц сайта    //
                    echo $value."\n";                /////////////////////
                }
            }
	}

	function Parsing($url)
	{
		//$rooturl='http://kristall-kino.ru'; //href=\"\/user\/regis
		global $countDown;
		global $countObject;
        $client = new Client();                               //создаем запрос
        $response = $client->createRequest()->setUrl($url)    //
        	->setOptions(['userAgent'=> 'Googlebot', 'proxy' => 'tcp://124.88.67.63:80'])->send();
        if($response->isOk) //если ответ ок, то применяем паттерн
        {
        	//$pattern1 = "/<a.*href=\"\/(.*)\">/Uis"; //для ссылок
        	$pattern = "#\s(?:href|src|url)=(?:[\"\'])?([^http,mailto,\#,\"].*?)(?:[\"\'])?(?:[\s\>])#i";//для всего
        	preg_match_all($pattern, $response->content, $out); //ищем ссылки по паттерну
        	//var_dump($out); //css js jpg png
			foreach($out[1] as $value)
			{
				$url1 = self::$rooturl.$value; //забиваем найденную ссылку
				if (substr_count($value, '\/')) {continue;}
                if ((substr_count($value, '.jpg')||substr_count($value, '.JPG')||substr_count($value, '.PNG')||substr_count($value, '.png')||substr_count($value, '.css')||substr_count($value, '.CSS')||substr_count($value, '.js')||substr_count($value, '.JS')||substr_count($value, '.swf')||substr_count($value, '.SWF')||substr_count($value, '.ico')||substr_count($value, '.ICO'))>0) 
                	{
                		//echo "Пробую ".$url1.".\n";
                		$count = Objects::find()->where(['url' => $url1])->count(); //ищем совпадения в базе объектов
        				if ($count>0) //если совпадения найдены, то скипаем
                            {
                                /*$find = Objects::find()->where(['url' => $url1])->one();////////////
                                if ($find->hash==hash_test($response->content)) {*/continue;/*}
                                else $izm =*/
                            }
        				$client = new Client(); //создаем запрос для ссылки со страницы
        				$response = $client->createRequest()->setUrl($url1)
        					->setOptions(['userAgent'=> 'Googlebot', 'proxy' => 'tcp://124.88.67.63:80'])->send();
        				if($response->isOk)//если ответ ок, то добавляем в базу
        				{
        					$countObject = $countObject+1; //счет выгруженных ссылок
                            if (!is_dir('site')) mkdir("site", 0777); //создаем папку для файлов
        					echo "Выгружаю в базу ".$url1."\n";
            				$modelObjects = new Objects;	//создаем экземпляр объекта таблицы
                            file_put_contents("site/" . $this->hash_test($url1),$response->content);//сохраняем файл на диск
            				$modelObjects->hash = $this->hash_test($response->content);//хэш страницы
            				//$modelObjects->urlhash = $this->hash_test($url1);//юрл-хэш
							$modelObjects->url = $url1;//юрл
							$modelObjects->save();//сохраняем
			    		} else {echo "Проблема соединения"; array_push($avail,$url1);}//если соединение не удалось,
                		continue;                                                     //то добавляем в массив недост. юрл
                	}
        		$count = Urls::find()->where(['url' => $url1])->count(); //ищем совпадения в базе ссылок
        		if ($count>0) {continue;} //если совпадения найдены - скип
        		$client = new Client();
        		$response = $client->createRequest()->setUrl($url1)
        		->setOptions(['userAgent'=> 'Googlebot', 'proxy' => 'tcp://124.88.67.63:80'])->send();
        		if($response->isOk)
        		{
        			$countDown = $countDown+1; //счет выгруженных ссылок
        			echo "Выгружаю в базу ".$url1."\n";
                    if (!is_dir('site')) mkdir("site", 0777); //папка для файлов
            		$modelUrls = new Urls;	
                    file_put_contents("site/" . $this->hash_test($url1),$response->content);///
            		$modelUrls->hash = $this->hash_test($response->content);
					$modelUrls->url = $url1;
					//$modelUrls->urlhash = $this->hash_test($url1);
                    $modelUrls->ping = '-';//пинг
					$modelUrls->save();
			    } else {echo "Проблема соединения"; array_push($avail,$url1);}//если ошибка, запись в массив ошибок
			}
        } else {echo "Ошибка соединения\n"; array_push($avail,$url1);}
	}

    public function actionCheckDiff() //проверка уже существующих ссылок
    {
        $izm[]='';
        //последовательное чтение всех ссылок из базы с расчетом и сравнением хэша
        $find = Urls::find()->all();
        foreach($find as $value)
        {
            //echo $value->url . "\n";
            $client = new Client();
            $response = $client->createRequest()->setUrl($value->url)
            ->setOptions(['userAgent'=> 'Googlebot', 'proxy' => 'tcp://124.88.67.63:80'])->send();
            //echo $value->url."\n";
            if($response->isOk)
            {
                if ($value->hash!=$this->hash_test($response->content))
                {

                    //echo $value->hash . '<>' . $this->hash_test($response->content) . "!!!\n";
                    array_push($izm, $value->url);
                    file_put_contents("filediff.html", $response->content);
                    echo "\nСравниваю файл:".$value->url."\n";
                    $this->Differences($this->hash_test($value->url),"filediff.html", $value->url);
                }
            } else echo "Что-то пошло не так! (проблема соединения)\n";
        }
        $find = Objects::find()->all();
        foreach($find as $value)
        {
            //echo $value->url . "\n";
            $client = new Client();
            $response = $client->createRequest()->setUrl($value->url)
            ->setOptions(['userAgent'=> 'Googlebot', 'proxy' => 'tcp://124.88.67.63:80'])->send();
            if($response->isOk)
            {
                if ($value->hash!=$this->hash_test($response->content))
                {

                    //echo $value->hash . '<>' . $this->hash_test($response->content) . "!!!\n";
                    array_push($izm, $value->url);
                    file_put_contents("filediff.html", $response->content);
                    $this->Differences($this->hash_test($value->url),"filediff.html", $value->url);
                }
            } else echo "Что-то пошло не так! (проблема соединения)\n";
        }
        echo "Изменения произошли на следующих страницах/файлах:";
        foreach($izm as $value)
        {

        	echo $value."\n";
            //Differences();
        }
        //var_dump($izm);
    }

	function hash_test($content) //принимает переменную с данными и выдает хэш
	{
		file_put_contents("File.html",$content);
		return hash_file('sha1', 'File.html');
		//unlink("File.html");
	}   

    function Differences($file1,$file2,$url)
    {
    //echo file_get_contents("site/".$file1);
    similar_text(file_get_contents("site/".$file1), file_get_contents($file2), $percent);
    //html префикс
    $fear1='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html><head><meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <title>CHECK DIFFERENCES ['.$url.']</title>
    <link rel="stylesheet" href="../styles.css" type="text/css" charset="utf-8"/>
    </head><body><h1>DIFFERENCES ['.$url.']</h1><br><h1>Процент совпадений - '.$percent.'%</h1><hr/>';
    //html постфикс
    $fear2 = '</pre></body></html>';
    $sitefolder='sitename';//передавать эту переменную скрипту
    require_once dirname(__FILE__).'/Difference/Diff.php';
    //echo  dirname(__FILE__).'/Difference/Diff.php';
    $a = explode("\n", file_get_contents("site/"./*dirname(__FILE__).'/'.*/$file1));
    $b = explode("\n", file_get_contents(/*dirname(__FILE__).'/'.*/$file2));
    //echo "file1 ".$file1."and file2 ".$file2."\n";

    $options = array(
        //'ignoreWhitespace' => true,
        //'ignoreCase' => true,
                    );

        // Initialize the diff class
    $diff = new \Diff($a, $b, $options);

    // Generate a side by side diff
    require_once dirname(__FILE__).'/Difference/Diff/Renderer/Html/SideBySide.php';
    $renderer = new \Diff_Renderer_Html_SideBySide;
    $resp=$diff->Render($renderer);
    if ($resp!='') 
    {
    	$out=$fear1 . $resp . $fear2;
    	if (!is_dir('commands/diffs/'.$sitefolder)) mkdir('commands/diffs/'.$sitefolder, 0777); //папка для файлов
    	//foreach (glob('commands/diffs/'.$sitefolder."/*") as $file) unlink($file);
    	file_put_contents('commands/diffs/'.$sitefolder.'/'.date('d.m_H:i').'-'.$file1.'.html', $out);
    	//FILE_APPEND^^^^^^^^^^
	}
    }

	    function pingsite($url) // П Е Р Е Д Е Л А Т Ь
    {
    	return '-';
        /*$ping = exec('ping -c2 ' . $url);
        preg_match("/\/([0-9]*\.[0-9]*)\/[0-9]*\./", $ping, $outping);
        if (empty($outping)) return '-';
            else return $outping[1];*/
    }
}    
//9c88f4ee1327f8e753f63b1fa3c19f448a510e54!!! Kristall-kino
