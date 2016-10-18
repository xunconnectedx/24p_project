<?php

namespace app\commands;
use yii;
use yii\web\Request;
use yii\console\Controller;
use app\models\Urls;
use app\models\Objects;
use yii\httpclient\Client;

class RevizController extends Controller
{
    public static $rooturl='http://kristall-kino.ru';
    public function actionIndex(/*$url*/) //первая запись в базу (корень)
    {
		//$url = 'http://kristall-kino.ru';
        $client = new Client();
        $response = $client->createRequest()
        	->setUrl(self::$rooturl)->setOptions(['userAgent'=> 'Googlebot'])
        	->send();
        if($response->isOk)
        {
        	$modelUrls = new Urls;	
        	$modelUrls->hash = $this->hash_test($response->content);
			$modelUrls->url = self::$rooturl;
			$modelUrls->parsed = 0;
            $modelUrls->ping = $this->pingsite(substr(self::$rooturl,7));//пинг
			$modelUrls->save();
        }
        else echo "Проблема соединения!\n";
    }

    public function actionWgetDownload()
    {
        exec('wget -q -r -l 0 -p -U Googlebot -P site ' . 'http://kristall-kino.ru');
    }

    public function actionWgetRec()
    {
        $this->WgetRecord('site');
    }

    function WgetRecord($dir)
    {
        if (!is_dir($dir))
        {
            echo "Directory not found! \n";
            return false;
        } 
        //$filemd5s = array();
        $files = scandir($dir); //Список всех файлов в данной дерриктории
        /*
        Пробегаем по всем файлов в данной деррикотории
        */
        foreach ($files as $value)
        {
            //unset($filemd5s);
            if (($value!= '.') && ($value != '..'))
            {
                //Если встретили папку, то окрываем еее
                if (is_dir($dir.'/'.$value))
                {
                //echo "$dir$value \n";
                $this->WgetRecord($dir.'/'.$value);
                }
                else
                {   
                    $val = $dir . "/" . $value;
                    echo "$val\n" . hash_file('sha1', $val) . "\n\n";
                    $modelUrls = new Urls;  
                    $modelUrls->hash = hash_file('sha1', $val);
                    $modelUrls->url = 'http://' . substr($val,5);
                    $modelUrls->parsed = 0;
                    $modelUrls->ping = $this->pingsite(substr(self::$rooturl,7));//пинг
                    $modelUrls->save();      

                }
            }
        }
    }

    function pingsite($url)
    {
        $ping = exec('ping -c2 ' . $url);
        preg_match("/\/([0-9]*\.[0-9]*)\/[0-9]*\./", $ping, $outping);
        if (empty($outping)) return '-';
            else return $outping[1];

    }

	public function actionPars()
	{
		//$rooturl='http://kristall-kino.ru';
		global $countDown;
		global $countObject;
		$Pars = Urls::find()->where(['parsed'=> 0])->all(); //выбираем не спарсенные
    	foreach($Pars as $value)
    	{
    		$oldCountDown = $countDown;
        	$this->Parsing($value['url']);
        	$modelUrls = Urls::find()//находим текущий и выставляем парс=1
    		->where(['url' => $value['url']])
    		->one();
			$modelUrls->parsed = 1; //раскомментить
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
        if ($endPars>0) 
        {
            $this->actionPars(); //вызов рекурсивный    
        } else {echo "Всего добавленно ".$countDown." ссылок и ".$countObject." объектов.\n";}
	}

	function Parsing($url)
	{
		//$rooturl='http://kristall-kino.ru'; //href=\"\/user\/regis
		global $countDown;
		global $countObject;
		$client = new Client();
        $response = $client->createRequest()->setUrl($url)
        	->setOptions(['userAgent'=> 'Googlebot'])->send();
        if($response->isOk)
        {
        	//$pattern1 = "/<a.*href=\"\/(.*)\">/Uis"; //для ссылок
        	$pattern = "#\s(?:href|src|url)=(?:[\"\'])?([^http,mailto,\#,\"].*?)(?:[\"\'])?(?:[\s\>])#i";//для всего
        	preg_match_all($pattern, $response->content, $out);
        	//var_dump($out); //css js jpg png
			foreach($out[1] as $value)
			{
				$url1 = self::$rooturl.$value; //забиваем найденную ссылку
				if (substr_count($value, '\/')) {continue;}
                if ((substr_count($value, '.jpg')||substr_count($value, '.JPG')||substr_count($value, '.PNG')||substr_count($value, '.png')||substr_count($value, '.css')||substr_count($value, '.CSS')||substr_count($value, '.js')||substr_count($value, '.JS')||substr_count($value, '.swf')||substr_count($value, '.SWF')||substr_count($value, '.ico')||substr_count($value, '.ICO'))>0) 
                	{
                		//echo "Пробую ".$url1.".\n";
                		$count = Objects::find()->where(['url' => $url1])->count(); //ищем совпадения в базе
        				if ($count>0) {continue;}
        				$client = new Client();
        				$response = $client->createRequest()->setUrl($url1)
        					->setOptions(['userAgent'=> 'Googlebot'])->send();
        				if($response->isOk)
        				{
        					$countObject = $countObject+1; //счет выгруженных ссылок
        					echo "Выгружаю в базу ".$url1."\n";
            				$modelObjects = new Objects;	
            				$modelObjects->hash = $this->hash_test($url1);
							$modelObjects->url = $url1;
							$modelObjects->save();
			    		} else echo "Проблема соединения";
                		continue;
                	}
        		$count = Urls::find()->where(['url' => $url1])->count(); //ищем совпадения в базе
        		if ($count>0) {continue;}
        		$client = new Client();
        		$response = $client->createRequest()->setUrl($url1)
        		->setOptions(['userAgent'=> 'Googlebot'])->send();
        		if($response->isOk)
        		{
        			$countDown = $countDown+1; //счет выгруженных ссылок
        			echo "Выгружаю в базу ".$url1."\n";
            		$modelUrls = new Urls;	
            		$modelUrls->hash = $this->hash_test($url1);
					$modelUrls->url = $url1;
                    $modelUrls->ping = $this->pingsite($url1);//пинг
					$modelUrls->save();
			    } else echo "Проблема соединения";
			}
        } else {echo "Ошибка соединения\n";}
	}

	function hash_test($content) //принимает переменную с данными и выдает хэш
	{
		file_put_contents("File.html",$content);
		return hash_file('sha1', 'File.html');
		//unlink("File.html");
	}   
}    