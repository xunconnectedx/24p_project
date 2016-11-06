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
            //mkdir(substr(self::$rooturl,7), 0700);
            //file_put_contents(substr(self::$rooturl,7) . '/' . substr(self::$rooturl,7),$response->content);
        	$modelUrls = new Urls;	
        	$modelUrls->hash = $this->hash_test($response->content);
			$modelUrls->url = self::$rooturl;
            //echo $modelUrls->url;
			$modelUrls->parsed = 0;
			$modelUrls->urlhash = $this->hash_test(self::$rooturl);
            $modelUrls->ping = $this->pingsite(substr(self::$rooturl,7));//пинг
			$modelUrls->save();
        }
        else echo "Проблема соединения!\n";
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
        } else 
            {
                $find = Urls::find()->all();
                foreach($find as $value)
                {
                    $value->parsed = 0;
                    $value->save();   
                }
                echo "Всего добавленно ".$countDown." ссылок и ".$countObject." объектов.\n";
            }
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
        				if ($count>0) 
                            {
                                /*$find = Objects::find()->where(['url' => $url1])->one();////////////
                                if ($find->hash==hash_test($response->content)) {*/continue;/*}
                                else $izm =*/
                            }
        				$client = new Client();
        				$response = $client->createRequest()->setUrl($url1)
        					->setOptions(['userAgent'=> 'Googlebot'])->send();
        				if($response->isOk)
        				{
        					$countObject = $countObject+1; //счет выгруженных ссылок
                            if (!is_dir('site')) mkdir("site", 0777); //создаем папку для файлов
        					echo "Выгружаю в базу ".$url1."\n";
            				$modelObjects = new Objects;	
                            file_put_contents("site/" . $this->hash_test($url1),$response->content);///
            				$modelObjects->hash = $this->hash_test($response->content);
            				$modelObjects->urlhash = $this->hash_test($url1);
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
                    if (!is_dir('site')) mkdir("site", 0777); //папка для файлов
            		$modelUrls = new Urls;	
                    file_put_contents("site/" . $this->hash_test($url1),$response->content);///
            		$modelUrls->hash = $this->hash_test($response->content);
					$modelUrls->url = $url1;
					$modelUrls->urlhash = $this->hash_test($url1);
                    $modelUrls->ping = '-';//пинг
					$modelUrls->save();
			    } else echo "Проблема соединения";
			}
        } else {echo "Ошибка соединения\n";}
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
        echo "Изменения произошли на следующих страницах/файлах:\n";
        foreach($izm as $value)
        {
        	echo $value."\n";
        }
        //var_dump($izm);
    }

	function hash_test($content) //принимает переменную с данными и выдает хэш
	{
		file_put_contents("File.html",$content);
		return hash_file('sha1', 'File.html');
		//unlink("File.html");
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
