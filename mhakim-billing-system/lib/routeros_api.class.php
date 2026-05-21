<?php

class RouterosAPI {

    var $debug = false;

    var $connected = false;

    var $port = 8728;

    var $ssl = false;

    var $timeout = 3;

    var $attempts = 3;

    var $socket;
    var $error_no;
    var $error_str;

    public function connect($ip, $login, $password) {

        for ($ATTEMPT = 1; $ATTEMPT <= $this->attempts; $ATTEMPT++) {

            $this->connected = false;

            $PROTOCOL = $this->ssl ? 'ssl://' : '';

            $this->socket = @stream_socket_client(
                $PROTOCOL . $ip . ":" . $this->port,
                $this->error_no,
                $this->error_str,
                $this->timeout
            );

            if ($this->socket) {

                socket_set_timeout($this->socket, $this->timeout);

                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=password=' . $password);

                $READ = $this->read(false);

                if (isset($READ[0]) && $READ[0] == '!done') {
                    $this->connected = true;
                    break;
                }
            }
        }

        return $this->connected;
    }

    public function disconnect() {
        if ($this->socket) {
            fclose($this->socket);
        }
        $this->connected = false;
    }

    public function write($command, $param2 = true) {

        if ($param2) {
            $command = $command . chr(0);
        }

        fwrite($this->socket, chr(strlen($command)) . $command);
    }

    public function read($parse = true) {

        $RESPONSE = [];

        while (true) {

            $BYTE = ord(fread($this->socket, 1));

            if ($BYTE) {

                $LINE = "";

                while ($BYTE-- > 0) {
                    $LINE .= fread($this->socket, 1);
                }

                $RESPONSE[] = $LINE;

                if ($LINE == '!done') {
                    break;
                }

            } else {
                break;
            }
        }

        return $RESPONSE;
    }

    public function comm($com, $arr = []) {

        $this->write($com, false);

        foreach ($arr as $k => $v) {
            $this->write('=' . $k . '=' . $v, false);
        }

        $this->write('');

        return $this->read();
    }
}
?>
