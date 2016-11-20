<?php
namespace app\commands;
use yii\console\Controller;
//use app\models\Ping;
/**
 * Receive data about ping
 */
class PingReceiveController extends Controller
{
	public function actionPingReceive(){
		date_default_timezone_set("Asia/Omsk");
		$socket = stream_socket_server("tcp://127.0.0.1:8000", $errno, $errstr);
		while ($connect = stream_socket_accept($socket, -1)) {	
   	 		$input = fread($connect, 8196);
    		fclose($connect);
    		preg_match_all('/\s([0-9.-]*)/', $input, $output);
    		$modelPing = new Ping;
    		$modelPing->time = date("H:i:s");
    		$modelPing->date = date("Y-m-d");
	    	$modelPing->google = $output[1][0];
	    	$modelPing->yandex = $output[1][1];
	    	$modelPing->mail = $output[1][2];
	    	$modelPing->bing = $output[1][3];
	    	$modelPing->yahoo = $output[1][4];
	    	$modelPing->save(false);
	}
	fclose($socket);
	}
	
}
?>