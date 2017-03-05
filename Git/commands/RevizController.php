<?php

namespace app\commands;

use app\models\Objects;
use app\models\Urls;
use yii\console\Controller;
use yii\httpclient\Client;

class RevizController extends Controller
{
    public $avail = array();
    public $idsite;
    public $proxy = 'tcp://124.88.67.10:81';
    public $rooturl; //='http://kristall-kino.ru';//домен для проверки

    public function Index() //первая запись в базу (корень)

    {
        $count = Urls::find()->where(['url' => $this->rooturl])->count(); //ищем совпадения в базе
        if ($count == 0) //если домен уже есть, то скипаем
        {

            $client = new Client();
            $time1 = microtime(true); //сомнительный расчет
            $response = $client->createRequest()
                ->setUrl($this->rooturl)->setOptions(['userAgent' => 'Googlebot', 'proxy' => $this->proxy])
                ->send();
            if ($response->isOk) {
                $time2 = microtime(true); //сомнительный расчет
                $timedelay = ($time2 - $time1) * 1000; //милисек
                $modelUrls = new Urls;
                $modelUrls->hash = $this->hashTest($response->content);
                $modelUrls->url = $this->rooturl;
                if (!is_dir('siteCheck')) {
                    mkdir('siteCheck', 0777);
                }

                if (!is_dir('siteCheck/' . rawurlencode($this->rooturl))) {
                    mkdir('siteCheck/' . rawurlencode($this->rooturl), 0777);
                }

                file_put_contents("siteCheck/" . rawurlencode($this->rooturl) . "/" . $this->hashTest($this->rooturl), $response->content); ///
                $modelUrls->parsed = 0;
                $modelUrls->datech = date('Y-m-d'); //дата проверки
                $modelUrls->ping = substr($timedelay, 0, 8); //пинг //сомнительный расчет
                $modelUrls->DownlTime = substr($timedelay, 0, 8) / 1000 + $this->TimeDwnld($response->content); //вызов функции подсчета времени загрузки элементов в секундах
                $modelUrls->sites_id = $this->idsite;
                $modelUrls->save();
            } else {
                echo "Проблема соединения! (" . $this->rooturl . ")\n";
            }

        }
    }

    public function Pars() //парсинг из базы (по параметру parsed=0)

    {
        global $countDown;
        global $countObject;
        $Pars = Urls::find()->where(['parsed' => 0])->all(); //выбираем не спарсенные
        foreach ($Pars as $value) {
            $oldCountDown = $countDown;
            $this->Parsing($value['url']); //вызов функции поиска ссылок на странице
            $modelUrls = Urls::find() //находим рабочий элемент и выставляем парс=1
                ->where(['url' => $value['url']])
                ->one();
            $modelUrls->parsed = 1; //указываем, что ссылка уже парсилась
            $modelUrls->save();
            echo "проработал " . $value['url'] . "\n";
            if ($countDown > $oldCountDown) {
                //$countd=$countDown-$oldCountDown;
                //echo "В базу выгружено ".$countd." ссылок.\n";
                break;
            } else {echo "Новые ссылки не найдены.^^^\n";}
        }
        $endPars = Urls::find()->where(['parsed' => 0])->count();
        if ($endPars > 0) //если остались не спарсенные ссылки, то продолжаем
        {
            $this->Pars(); //вызов рекурсивный
        } else {
            $find = Urls::find()->all(); /////////////////////
            foreach ($find as $value) //Очищаем поле парс//
            { //во всех элементах//
                $value->parsed = 0; //базы             //
                $value->save(); //                 //
            } /////////////////////
            echo "Всего добавленно " . $countDown . " ссылок и " . $countObject . " объектов.\n"; //вывод кол-ва обр. объектов
            echo "Недоступные страницы/файлы:\n"; /////////////////////
            foreach ($this->avail as $value) //Вывод недоступных//
            { //повторная проверка С П Р О С И Т Ь
                //$this->controlCheck($value);// //страниц сайта    //
                echo $value . "\n"; /////////////////////
            }
        }
    }

    public function Parsing($url)
    {
        //$rooturl='http://kristall-kino.ru'; //href=\"\/user\/regis
        global $countDown;
        global $countObject;
        //if (substr_count($url, '.js') && substr_count($url, '.css') == 0) {
        $client = new Client(); //создаем запрос
        $response = $client->createRequest()->setUrl($url) //
            ->setOptions(['userAgent' => 'Googlebot', 'proxy' => $this->proxy])->send();
        if ($response->isOk) //если ответ ок, то применяем паттерн
        {
            //$pattern1 = "/<a.*href=\"\/(.*)\">/Uis"; //для ссылок
            $pattern = "#\s(?:href|src|url)=(?:[\"\'])?([^http,mailto,\#,\"].*?)(?:[\"\'])?(?:[\s\>])#i"; //для всего
            preg_match_all($pattern, $response->content, $out); //ищем ссылки по паттерну
            //var_dump($out); //css js jpg png

            //ЗДЕСЬ МОЖНО СДЕЛАТЬ ПРИВЯЗКУ К ЮРЛ,
            //НА КОТОРОЙ НАЙДЕТСЯ ОБЪЕКТ

            foreach ($out[1] as $value) {
                $url1 = $this->rooturl . $value; //забиваем найденную ссылку
                if (substr_count($value, '\/')) {continue;}
                if ($this->findobject($url1)) {
                    //echo "Пробую ".$url1.".\n";
                    $count = Objects::find()->where(['url' => $url1])->count(); //ищем совпадения в базе объектов
                    if ($count > 0) //если совпадения найдены, то скипаем
                    {
                        continue;
                    }
                    $client = new Client(); //создаем запрос для ссылки со страницы
                    $response = $client->createRequest()->setUrl($url1)
                        ->setOptions(['userAgent' => 'Googlebot', 'proxy' => $this->proxy])->send();
                    if ($response->isOk) //если ответ ок, то добавляем в базу
                    {
                        $countObject = $countObject + 1; //счет выгруженных ссылок
                        if (!is_dir('siteCheck')) {
                            mkdir("siteCheck", 0777);
                        }
                        //создаем папку для файлов
                        if (!is_dir('siteCheck/' . rawurlencode($this->rooturl))) {
                            mkdir('siteCheck/' . rawurlencode($this->rooturl), 0777);
                        }

                        echo "Выгружаю в базу " . $url1 . "\n";
                        $modelObjects = new Objects; //создаем экземпляр объекта таблицы
                        file_put_contents("siteCheck/" . rawurlencode($this->rooturl) . "/" . $this->hashTest($url1), $response->content); //сохраняем файл на диск
                        $modelObjects->hash = $this->hashTest($response->content); //хэш страницы

                        //А ТУТ СДЕЛАТЬ ЗАПИСЬ, ЧТО ОБЪЕКТ ОТНОСИТСЯ К ТОЙ ССЫЛКЕ

                        $modelObjects->url = $url1; //юрл
                        $modelObjects->datech = date('Y-m-d'); //дата проверки
                        $modelObjects->sites_id = $this->idsite; // И З М Е Н И Т Ь (все уже?)
                        $modelObjects->save(); //сохраняем
                    } else {
                        echo "Проблема соединения! (" . $url1 . ")\n";
                        array_push($this->avail, $url1);} //если соединение не удалось,
                    continue; //то добавляем в массив недост. юрл
                }
                $count = Urls::find()->where(['url' => $url1])->count(); //ищем совпадения в базе ссылок
                if ($count > 0) {continue;} //если совпадения найдены - скип
                $client = new Client();
                $time1 = microtime(true);
                $response = $client->createRequest()->setUrl($url1)
                    ->setOptions(['userAgent' => 'Googlebot', 'proxy' => $this->proxy])->send();
                if ($response->isOk) {
                    $time2 = microtime(true);
                    $timedelay = ($time2 - $time1) * 1000; //милисек
                    $countDown = $countDown + 1; //счет выгруженных ссылок
                    echo "Выгружаю в базу " . $url1 . "\n";
                    if (!is_dir('siteCheck')) {
                        mkdir("siteCheck", 0777);
                    }
                    //папка для файлов
                    if (!is_dir('siteCheck/' . rawurlencode($this->rooturl))) {
                        mkdir('siteCheck/' . rawurlencode($this->rooturl), 0777);
                    }

                    $modelUrls = new Urls;
                    file_put_contents("siteCheck/" . rawurlencode($this->rooturl) . "/" . $this->hashTest($url1), $response->content); ///
                    $modelUrls->hash = $this->hashTest($response->content);
                    $modelUrls->url = $url1;
                    $modelUrls->ping = substr($timedelay, 0, 8); //пинг хтмл
                    $modelUrls->DownlTime = substr($timedelay, 0, 8) / 1000 + $this->TimeDwnld($response->content); //вызов функции подсчета времени загрузки элементов в секундах
                    $modelUrls->datech = date('Y-m-d'); //дата проверки
                    $modelUrls->sites_id = $this->idsite; // И З М Е Н И Т Ь
                    $modelUrls->save();
                } else {
                    echo "Проблема соединения" . $url1 . ")\n";
                    array_push($this->avail, $url1);} //если ошибка, запись в массив ошибок
            }
        } else {
            echo "Ошибка соединения" . $url . ")\n";
            array_push($this->avail, $url);}
        //}
    }

    public function TimeDwnld($content)
    {
        //возвращает время загрузки элементов страницы
        //echo $response->content;
        $pattern = "#\s(?:src)=(?:[\"\'])*?([^mailto,\#,\"].*?)\"#i";
        $pattern2 = "#\s(?:href)=(?:[\"\'])*?([^mailto,\#,\"].*?)\"#i"; //нужен доп отсев по css/ico
        preg_match_all($pattern, $content, $out); //ищем объекты страницы по паттерну
        preg_match_all($pattern2, $content, $out1); //ищем объекты страницы по паттерну
        //var_dump($out1);
        $atime = 0;
        foreach ($out[1] as $value) {
            if (substr_count($value, 'http://') == 0) {
                $value = $this->rooturl . $value;
            }

            $atime = $atime + $this->DwnldTime($value); //время в секундах
        }
        foreach ($out1[1] as $value) {
            if ((substr_count($value, '.css') || substr_count($value, '.ico')) > 0) {
                $value = $this->rooturl . $value;
                $atime = $atime + $this->DwnldTime($value);
            }
        }
        return $atime;
    }

    public function CheckDiff() //проверка уже существующих ссылок

    {
        $izm[] = '';
        //последовательное чтение всех ссылок из базы с расчетом и сравнением хэша
        $find = Urls::find()->all();
        foreach ($find as $value) {
            //echo $value->url . "\n";
            $client = new Client();
            $response = $client->createRequest()->setUrl($value->url)
                ->setOptions(['userAgent' => 'Googlebot', 'proxy' => $this->proxy])->send();
            //echo $value->url."\n";
            if ($response->isOk) {
                if ($value->hash != $this->hashTest($response->content)) //если найдены отличия
                {
                    array_push($izm, $value->url);
                    file_put_contents("filediff.html", $response->content);
                    echo "Сравниваю файл:" . $value->url . "\n";
                    $this->Differences($this->hashTest($value->url), "filediff.html", $value->url);
                }
            } else {
                echo "Что-то пошло не так! (проблема соединения)\n";
            }

        }
        $find = Objects::find()->all();
        foreach ($find as $value) {
            if ($this->findobject($value->url)) {continue;} //если это объект (проверочка)

            //echo $value->url . "\n";
            $client = new Client();
            $response = $client->createRequest()->setUrl($value->url)
                ->setOptions(['userAgent' => 'Googlebot', 'proxy' => $this->proxy])->send();
            if ($response->isOk) {
                if ($value->hash != $this->hashTest($response->content)) {

                    //echo $value->hash . '<>' . $this->hash_test($response->content) . "!!!\n";
                    array_push($izm, $value->url);
                    file_put_contents("filediff.html", $response->content);
                    $this->Differences($this->hashTest($value->url), "filediff.html", $value->url);
                }
            } else {
                echo "Что-то пошло не так! (проблема соединения)\n";
            }

        }
        echo "\nИзменения произошли на следующих страницах/файлах:";
        foreach ($izm as $value) {

            echo $value . "\n";
            //Differences();
        }
        //var_dump($izm);
    }

    public function findobject($url) //проверка на наличие в юрл расширения объекта

    {
        if ((substr_count($url, '.jpg') || substr_count($url, '.JPG') || substr_count($url, '.PNG') || substr_count($url, '.png') || substr_count($url, '.swf') || substr_count($url, '.SWF') || substr_count($url, '.ico') || substr_count($url, '.ICO') || substr_count($url, '.css') || substr_count($url, '.js')) > 0) {
            return 1;
        } else {
            return 0;
        }

    }

    public function hashTest($content) //принимает переменную с данными и выдает хэш

    {
        file_put_contents("File.html", $content);
        return hash_file('sha1', 'File.html');
        //unlink("File.html");
    }

    public function DwnldTime($url) //херь

    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        if (!curl_errno($ch)) {
            $info = curl_getinfo($ch);
            return $info['total_time'];
            //echo '' . $info['size_download'] . "\n" . $info['speed_download'] . '  ' . $info['total_time'] . ' Секунд при загрузка адреса ' . $info['url'] . "\n";
        }
        curl_close($ch);
    }

    public function Differences($file1, $file2, $url)
    {
        //echo file_get_contents("site/".$file1);
        similar_text(file_get_contents("siteCheck/" . rawurlencode($this->rooturl) . "/" . $file1), file_get_contents($file2));
        //html префикс
        $fear1 = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html><head><meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <title>CHECK DIFFERENCES [' . $url . ']</title>
    <link rel="stylesheet" href="../styles.css" type="text/css" charset="utf-8"/>
    </head><body><h1>DIFFERENCES [' . $url . ']</h1><br><h1>Процент совпадений - ';
        $fear2 = '%</h1><hr/>'; //Возможно придется отказаться от этой функции (считает правильно раз через раз)
        //html постфикс
        $fear3 = '</pre></body></html>';
        $sitefolder = rawurlencode($this->rooturl); //передавать эту переменную скрипту
        require_once dirname(__FILE__) . '/Difference/Diff.php';
        //echo  dirname(__FILE__).'/Difference/Diff.php';
        $a = explode("\n", file_get_contents("siteCheck/" . rawurlencode($this->rooturl) . "/" . /*dirname(__FILE__).'/'.*/$file1));
        $b = explode("\n", file_get_contents( /*dirname(__FILE__).'/'.*/$file2));
        //echo "file1 ".$file1."and file2 ".$file2."\n";

        $options = array(
            'context' => 0,
            'ignoreNewLines' => true,
            'ignoreWhitespace' => true,
            'ignoreCase' => true,
        );

        // Initialize the diff class
        $diff = new \Diff($a, $b, $options);

        // Generate a side by side diff
        require_once dirname(__FILE__) . '/Difference/Diff/Renderer/Html/SideBySide.php';
        $renderer = new \Diff_Renderer_Html_SideBySide;
        $resp = $diff->Render($renderer);
        $acount = count($a);
        $bcount = count($b);
        if ($acount > $bcount) {
            $stcount = $acount;
        } else {
            $stcount = $bcount;
        }

        $percent = (($stcount - substr_count($resp, "<tr>") - 1) / $stcount) * 100;

        echo "Различия найдены!\n";
        $out = $fear1 . $percent . $fear2 . $resp . $fear3;
        if (!is_dir('commands/diffs/' . $sitefolder)) {
            mkdir('commands/diffs/' . $sitefolder, 0777);
        }
        //папка для файлов
        //foreach (glob('commands/diffs/'.$sitefolder."/*") as $file) unlink($file);
        file_put_contents('commands/diffs/' . $sitefolder . '/' . date('d.m_H:i') . '-' . $file1 . '.html', $out);
    }

    public function VirusTotal($url)
    {
        require_once 'VirusTotalApiV2.php';
        //e4b1660e6c71e69ae8266510158b59e66884e9919cb805c3e39ba2ada9a96d50
        /* Initialize the VirusTotalApi class. */
        $api = new \VirusTotalAPIV2('e4b1660e6c71e69ae8266510158b59e66884e9919cb805c3e39ba2ada9a96d50');

        /* Scan an URL. */
        $result = $api->scanURL($url);
        $scanId = $api->getScanID($result); /* Can be used to check for the report later on. */
        $res = explode('-', $scanId);
        echo ("https://www.virustotal.com/url/" . $res[0] . "/analysis/" . $res[1] . "/\n");
        //$api->displayResult($result);

    }

    //http://api.urlvoid.com/api1000/4fb13462b92ab6c95206e90ec6a4eeaaa3ab6b53/host/google.com/  //URLVOID

    //https://sitecheck.sucuri.net/results/konti-kino.ru

    /*public function controlCheck($url) //все ссылки, которые объявлены недоступными проходят повторную проверку

    {
    $client = new Client();
    $response = $client->createRequest()
     *//*])
->send();
if (!$response->isOk) {
//не все ссылки еще есть в базе, некоторых недоступных в базе нет
//нужно что-то другое
}

}*/

}; // 1. Если страница исчезла, пометку сделать в базе+когда исчезла;
//разгрузить код? добавить функций процедур
