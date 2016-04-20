<?php

return array(
    'database' => array(
        'hostname' => '127.0.0.1',
        'socket' => 'host', //host or unix_socket
        'username' => 'postgres',
        'password' => 'renato',
        'port_number' => '5432',
        'db_name' => 'lbag',
        'config' => array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        )
    ),
);