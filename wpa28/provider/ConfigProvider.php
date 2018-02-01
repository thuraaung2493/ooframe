<?php

class Config {
    public function get($config) {
        $e_config = explode(".", $config);
        $config = require DD . '/app/config/' . $e_config[0] . ".php";
        return $config[$e_config[1]];
    }
}

 ?>
