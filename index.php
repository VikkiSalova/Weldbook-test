<?php

require_once ('config/Config.php');
require_once ('core/Api.php');

try {
    $api = new Api(Config::HOST, Config::USER, Config::PASSWORD);
    echo $api->run();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}