<?php

class myDB {

    private $dbUser = '';
    private $dbPassword = '';
    private $dbHost = '';
    private $dbName = '';
    private $dbLink = false;

    public function __construct($host, $user, $password) {
        $this->dbUser = $user;
        $this->dbPassword = $password;
        $this->dbHost = $host;
        $this->Connect();
    }

    public function Connect() {
        $this->dbLink = mysql_connect($this->dbHost, $this->dbUser, $this->dbPassword);
        if ($this->dbLink == false)
            die('Could not connect: ' . mysql_error());
    }

    public function SelectDB($dbname) {
        $this->dbName = $dbname;
        mysql_select_db($this->dbName)
                or die('Could not select database: ' . $this->dbName);
    }

    public function Close() {
        mysql_close($this->dbLink);
    }

    public function PrintQueryResults($query) {
        $result = $this->MakeQuery($query);
        echo "\n<table>\n";
        echo "<tr>\n";
        echo "<th<strong>A/A</strong></th>\n";
        $fields = $this->GetFieldNames($result);
        foreach ($fields as $field_name) {
            echo "<th><strong>$field_name</strong></th>\n";
        }
        echo "</tr>\n";
        $i = 1;
        while ($record = $this->GetRecord($result)) {
            echo "<tr>\n";
            echo"<td valign=\"top\"><strong>$i.</strong></td>\n";
            foreach ($record as $field_value) {
                echo "<td valign=\"top\">$field_value</td>\n";
            }
            echo "</tr>\n";
            $i++;
        }
        echo "</table>\n";
    }

    public function MakeQuery($query) {
        $result = mysql_query($query, $this->dbLink) or die("QUERY '$quer' failed: " . mysql_error());
        return $result;
    }

    public function GetRecord($result) {
        return mysql_fetch_array($result, MYSQL_ASSOC);
    }

    public function GetFieldNames($result) {
        $fields = $this->GetFieldCount($result);
        for ($i = 0; $i < $fields; $i++) {
            $FieldNames[] = mysql_field_name($result, $i);
        }
        return $FieldNames;
    }

    public function GetFieldCount($result) {
        return mysql_num_fields($result);
    }

    public function GetRecordCount($result) {
        return mysql_num_rows($result);
    }

    public function GetLastInsertID() {
        return mysql_insert_id($this->dbLink);
    }

    public function GetAffectedRows() {
        return mysql_affected_rows($this->dbLink);
    }

    public function GetResultAsArray($result) {
        while ($record = $this->GetRecord($result)) {
            $data[] = $record;
        }
        return $data;
    }

}

?>