<?php

$socket = stream_socket_server("tcp://0.0.0.0:8000", $errno, $errstr);

if (!$socket) {
    die("$errstr ($errno)\n");
}

$connects = array();
while (true) {
    //формируем массив прослушиваемых сокетов:
    $read = $connects;
    $read []= $socket;
    $write = $except = null;

    if (!stream_select($read, $write, $except, null)) {//ожидаем сокеты доступные для чтения (без таймаута)
        break;
    }

    if (in_array($socket, $read)) {//есть новое соединение
        $connect = stream_socket_accept($socket, -1);//принимаем новое соединение
        $connects[] = $connect;//добавляем его в список необходимых для обработки
        unset($read[ array_search($socket, $read) ]);
    }

    foreach($read as $connect) {//обрабатываем все соединения
        $headers = '';
        while ($buffer = rtrim(fgets($connect))) {
            $headers .= $buffer;
        }
        fwrite($connect, "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\nConnection: close\r\n\r\nHello");
        fclose($connect);
        unset($connects[ array_search($connect, $connects) ]);
    }
}

fclose($server);

?>