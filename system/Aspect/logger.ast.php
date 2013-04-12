<?php
function logger($msg) {
	$today = date("d.m.Y");
	$filename = $config['logger']['logPath']."/$today.txt";
	$fd = fopen($filename, "c");
	$str = "[" . date("d/m/Y h:i:s", mktime()) . "] " . $msg;
	fwrite($fd, $str . PHP_EOL);
	fclose($fd);
}