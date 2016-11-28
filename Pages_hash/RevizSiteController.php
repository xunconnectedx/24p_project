<?php

namespace app\commands;
use yii;
use yii\web\Request;
use yii\console\Controller;
use app\models\Sites;
use yii\httpclient\Client;

class RevizSiteController extends Controller
{  	
	public function actionIndex() //
    {
		$Site1 = Sites::find()->all();
        foreach($Site1 as $value)
        {
            $check = new RevizController("1", "controller","");
            $check->rooturl=$value['url'];
            $check->actionIndex();//если уже есть, не добавлять
            $check->actionPars();
            $check->actionCheckDiff();
        }
    }
}
?>