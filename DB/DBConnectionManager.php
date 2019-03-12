<?php

class DBConnectionManager
{
    //ALTERVISTA UFFICIALE
    private $connection;
    private $host = "localhost";
    private $username = "root";
    private $passwd = "";
    private $dbname = "simplylife";

    function runConnection()
    {
        $this->connection = new mysqli($this->host, $this->username, $this->passwd, $this->dbname);
        return $this->connection;
    }
}
?>