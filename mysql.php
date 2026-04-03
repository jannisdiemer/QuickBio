<?php
    $host = "HOST";
    $name = "DB-NAME";
    $user = "USER-NAME";
    $passwort = "PASSWORD";
    try{
        $mysql = new PDO("mysql:host=$host;dbname=$name", $user, $passwort);
    } catch (PDOException $e){
        echo "SQL Error: ".$e->getMessage();
    }
?>