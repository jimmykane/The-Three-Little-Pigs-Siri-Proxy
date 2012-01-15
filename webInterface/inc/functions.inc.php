<?php

function getkeys() {
    extract($GLOBALS);
    $query = "SELECT * FROM `keys` WHERE expired='False' ";
    $result = $db->MakeQuery($query);
    $available_keys_count = $db->GetRecordCount($result);
    if ($available_keys_count > 0) {

        $keys = $db->GetResultAsArray($result);
        $keys[0]['availablekeys']=$available_keys_count;
        return $keys;
    }
    return false;
}

function getconfig() {
    extract($GLOBALS);
    $query = "SELECT * FROM `config` WHERE id=1 ";
    $result = $db->MakeQuery($query);
    $config = $db->GetRecord($result);
    return $config;
}

?>
