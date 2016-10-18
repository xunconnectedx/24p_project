<?php

namespace app\commands;
use yii;
use yii\web\Request;
use yii\console\Controller;
use app\models\Urls;
use app\models\Objects;
use yii\httpclient\Client;

class RevizWgetController extends Controller
{
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
}