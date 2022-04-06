<?php
declare(strict_types=1);

namespace App\Helper;

class Smsc{

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;


    /**
     * @var bool
     */
    private $is_post;

    /**
     * @var bool
     */
    private $is_https;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $from;

    /**
     * @var array
     */
    private $formats = array(1 => "flash=1", "push=1", "hlr=1", "bin=1", "bin=2", "ping=1", "mms=1", "mail=1", "call=1", "viber=1", "soc=1");

    public function __construct(string $login, string $password, bool $is_post, bool $is_https, string $charset, string $from){
        $this->login = $login;
        $this->password = $password;
        $this->is_post = $is_post;
        $this->is_https = $is_https;
        $this->charset = $charset;
        $this->from = $from;
    }

    /**
     * обязательные параметры:
     * @param string $phones - список телефонов через запятую или точку с запятой
     * @param string $message - отправляемое сообщение
     * 
     * необязательные параметры:
     * @param int $translit - переводить или нет в транслит (1,2 или 0)
     * @param string $time - необходимое время доставки в виде строки (DDMMYYhhmm, h1-h2, 0ts, +m)
     * @param int $id - идентификатор сообщения. Представляет собой 32-битное число в диапазоне от 1 до 2147483647.
     * @param int $format - формат сообщения (0 - обычное sms, 1 - flash-sms, 2 - wap-push, 3 - hlr, 4 - bin, 5 - bin-hex, 6 - ping-sms, 7 - mms, 8 - mail, 9 - call, 10 - viber, 11 - soc)
     * @param bool|string $sender - имя отправителя (Sender ID).
     * @param string $query - строка дополнительных параметров, добавляемая в URL-запрос ("valid=01:00&maxsms=3&tz=2")
     * @param array<mixed> $files - массив путей к файлам для отправки mms или e-mail сообщений
     * 
     * @return array возвращает массив (<id>, <количество sms>, <стоимость>, <баланс>) в случае успешной отправки
     */
    public function sendSms(string $phones, string $message, int $translit = 0, $time = 0, $id = 0, $format = 0, $sender = false, $query = "", $files = array()) :array{
        return $this->smscSendCmd("send", "cost=3&phones=".urlencode($phones)."&mes=".urlencode($message).
					"&translit=$translit&id=$id".($format > 0 ? "&".$this->formats[$format] : "").
					($sender === false ? "" : "&sender=".urlencode($sender)).
					($time ? "&time=".urlencode($time) : "").($query ? "&$query" : ""), $files);
    }

    /**
     * @param string $cmd - команда
     * @param string $arg - аругменты для выполнение команды
     * @param array $files - массив путей к файлам для отправки mms или e-mail сообщений
     * 
     */
    public function smscSendCmd(string $cmd, string $arg = "", array $files = array()) : array{
        $url = $_url = ($this->is_https ? "https" : "http")."://smsc.kz/sys/$cmd.php?login=".urlencode($this->login)."&psw=".urlencode($this->password)."&fmt=1&charset=".$this->charset."&".$arg;
        $i = 0;
        do {
            if ($i++)
                $url = str_replace('://smsc.kz/', '://www'.$i.'.smsc.kz/', $_url);
            $ret = $this->smscReadUrl($url, $files, 3 + $i);
        }
        while ($ret == "" && $i < 5);

        if ($ret == "") {
            $ret = ",";
        }

        $delim = ",";

        if ($cmd == "status") {
            parse_str($arg, $m);

            if (strpos($m["id"], ","))
                $delim = "\n";
        }

        return explode($delim, $ret);
    }

    /**
     * @param string $url
     * @param array $files
     * @param int $tm - timeout
     * 
     */
    public function smscReadUrl(string $url, ?array $files = array(), int $tm = 5)
    {
        $ret = "";
        $post = $this->is_post || strlen($url) > 2000 || $files;
    
        if (function_exists("curl_init"))
        {
            static $c = 0;
    
            if (!$c) {
                $c = curl_init();
                curl_setopt_array($c, array(
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CONNECTTIMEOUT => $tm,
                        CURLOPT_TIMEOUT => 60,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_HTTPHEADER => array("Expect:")
                        ));
            }
    
            curl_setopt($c, CURLOPT_POST, $post);
    
            if ($post)
            {
                list($url, $post) = explode("?", $url, 2);
    
                if ($files) {
                    parse_str($post, $m);
    
                    foreach ($m as $k => $v)
                        $m[$k] = isset($v[0]) && $v[0] == "@" ? sprintf("\0%s", $v) : $v;
    
                    $post = $m;
                    foreach ($files as $i => $path)
                        if (file_exists($path))
                            $post["file".$i] = function_exists("curl_file_create") ? curl_file_create($path) : "@".$path;
                }
    
                curl_setopt($c, CURLOPT_POSTFIELDS, $post);
            }
    
            curl_setopt($c, CURLOPT_URL, $url);
    
            $ret = curl_exec($c);
        }
        elseif ($files) {

        }
        else {
            if (!$this->is_https && function_exists("fsockopen"))
            {
                $m = parse_url($url);
    
                if (!$fp = fsockopen($m["host"], 80, $errno, $errstr, $tm))
                    $fp = fsockopen("212.24.33.196", 80, $errno, $errstr, $tm);
    
                if ($fp) {
                    stream_set_timeout($fp, 60);
    
                    fwrite($fp, ($post ? "POST $m[path]" : "GET $m[path]?$m[query]")." HTTP/1.1\r\nHost: smsc.kz\r\nUser-Agent: PHP".($post ? "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($m['query']) : "")."\r\nConnection: Close\r\n\r\n".($post ? $m['query'] : ""));
    
                    while (!feof($fp))
                        $ret .= fgets($fp, 1024);
                    list(, $ret) = explode("\r\n\r\n", $ret, 2);
    
                    fclose($fp);
                }
            }
            else
                $ret = file_get_contents($url);
        }
        return $ret;
    }
}

?>