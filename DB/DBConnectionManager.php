<?php

class DBConnectionManager
{
    //ALTERVISTA UFFICIALE
    private $connection;
    private $host = "localhost";
    private $username = "simplylife";
    private $passwd = "";
    private $dbname = "my_simplylife";

    function runConnection()
    {
        $this->connection = new mysqli($this->host, $this->username, $this->passwd, $this->dbname);
        return $this->connection;
    }
}

?>