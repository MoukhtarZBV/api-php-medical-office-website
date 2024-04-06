<?php
    function createConnection() {

        try {
            $PDO = new PDO("mysql:host=mysql-medical-office-ressources.alwaysdata.net;dbname=medical-office-ressources_bd", '350739', '$iutinfo');
        } catch (Exception $e) {
            echo $e; 
        }

        return $PDO;
    }
?>