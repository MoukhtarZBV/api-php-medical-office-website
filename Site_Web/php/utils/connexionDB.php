<?php
    function createConnection() {

        try {
            $PDO = new PDO("mysql:host=mysql-medical-office.alwaysdata.net;dbname=medical-office_auth", '350740', '$iutinfo');
        } catch (Exception $e) {
            echo $e; 
        }

        return $PDO;
    }
?>