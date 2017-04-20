<?php

    	$url="http://kristall-kino.ru"; //для проверки
    	$patt="https://validator.w3.org/nu/?doc=".rawurlencode($url)."&checkerrorpages=yes&showoutline=yes"; //собираем строку для запроса в curl
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $patt);
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; SunOS sun4u; en-US; rv:1.9b5) Gecko/2008032620 Firefox/3.0b5');
        $result = curl_exec($ch);
        curl_close($ch);
        file_put_contents(rawurlencode($url).'.html', $result);

        $patt="http://jigsaw.w3.org/css-validator/validator?uri=".rawurlencode($url)."&profile=css3&usermedium=all&warning=1&vextwarning=&lang=ru";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $patt);
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; SunOS sun4u; en-US; rv:1.9b5) Gecko/2008032620 Firefox/3.0b5');
        $result = curl_exec($ch);
        curl_close($ch);
        file_put_contents('w3css-'.rawurlencode($url).'.html', $result);

?>