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
    public static $rooturl='http://jomashop.com';
    public function actionWgetDownload()
    {
        exec('wget --random-wait 4 -q -E -r -l 0 -p --user-agent="Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko" -P site ' . 'http://jomashop.com');
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
                    //echo "$val\n" . hash_file('sha1', $val) . "\n\n";

                    if ((substr_count($value, '.jpg')||substr_count($value, '.JPG')||substr_count($value, '.PNG')||substr_count($value, '.png')||substr_count($value, '.css')||substr_count($value, '.CSS')||substr_count($value, '.js')||substr_count($value, '.JS')||substr_count($value, '.swf')||substr_count($value, '.SWF')||substr_count($value, '.ico')||substr_count($value, '.ICO')||substr_count($value, '.gif')||substr_count($value, '.GIF'))>0) 
                    {
                        $modelObjects = new Objects;    
                        $modelObjects->hash = hash_file('sha1', $val);
                        $modelObjects->url = 'http://' . substr($val,5);
                        $modelObjects->save();
                    } else {
                    preg_match("/(.*)[^.html]/i", $val,$out);
                    //echo $out[0];
                    $modelUrls = new Urls;  
                    $modelUrls->hash = hash_file('sha1', $val);
                    $modelUrls->url = 'http://' . substr($out[0],5);
                    $modelUrls->parsed = 0;
                    //echo "Пингую: ".substr(self::$rooturl,7)."\n";
                    $modelUrls->ping = '-';//пинг
                    $modelUrls->save();      
                    }
                }
            }
        }
    }

    function pingsite($url)// П Е Р Е Д Е Л А Т Ь
    {
        /*$ping = exec('ping -c2 ' . $url);
        preg_match("/\/([0-9]*\.[0-9]*)\/[0-9]*\./", $ping, $outping);
        if (empty($outping)) return '-';
            else return $outping[1];*/

    }
}