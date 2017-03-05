<?php

namespace app\commands;

use app\models\Sites;

class RevizSiteController extends RevizController {
	public function actionFull() //

	{
		$Site1 = Sites::find()->all();
		foreach ($Site1 as $value) {
			$this->idsite = $value->id; //выдираем айди домена
			$this->rooturl = $value['url'];
			$this->Index(); //если уже есть, не добавлять
			$this->Pars();
			$this->CheckDiff();
		}
	}

	public function actionDifferences() //

	{
		$Site1 = Sites::find()->all();
		foreach ($Site1 as $value) {
			$this->rooturl = $value['url'];
			$this->CheckDiff();
		}
	}

	public function actionVT() {
		$this->VirusTotal('tomsk.ru.on.nimp.org');
	}
}
