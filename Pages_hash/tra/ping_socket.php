<?php
// Timeout is in seconds
function ping($host, $timeout = 1) {
                /* ICMP ping packet with a pre-calculated checksum */
                $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
                // Узнать насчет прав для создания сокета
                $socket  = socket_create(AF_INET, SOCK_RAW, 1);
                socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
                socket_connect($socket, $host, null);
                $ping = array();
                for($i=0; $i<3; $i++){
                    $ts = microtime(true);
                    socket_send($socket, $package, strLen($package), 0);
                    if (socket_read($socket, 255))
                        $ping[$i] = ( microtime(true) - $ts ) * 1000;
                    else    $ping[$i] = false;
                } 
                socket_close($socket);
                $result = 0;
                foreach ($ping as $value) {
                    $result += $value;
                }
                return $result / 3;
        }
$pings = '';
$host ='en.wikipedia.org';
$pings = $host . ' ' . round(ping($host, 1), 3);
$socket = stream_socket_client('127.0.0.1:8000');
/* Отправить обычные данные через обычные каналы. */
fwrite($socket, $pings);
/* Закрыть сокет */
fclose($socket);
echo $pings;
?>