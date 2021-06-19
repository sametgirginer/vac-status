<?php
    $ini    =   parse_ini_file('config.ini');
    $host   =   $ini['host'];
    $db     =   $ini['dbname'];

    try {
        $db = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $ini['username'], $ini['password']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
?>