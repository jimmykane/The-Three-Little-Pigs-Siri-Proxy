<?php
function getkeys() {
    extract($GLOBALS);
    $query = "SELECT * FROM `keys` WHERE expired='False'";
    $result = $db->MakeQuery($query);
    $available_keys_count = $db->GetRecordCount($result);
    if ($available_keys_count > 0) {

        $keys = $db->GetResultAsArray($result);
        $keys[0]['availablekeys'] = $available_keys_count;
        return $keys;
    }
    return false;
}

function getexpiredkeys() {
    extract($GLOBALS);
    $query = "SELECT * FROM `keys` WHERE expired='True'";
    $result = $db->MakeQuery($query);
    $available_keys_count = $db->GetRecordCount($result);
    if ($available_keys_count > 0) {

        $keys = $db->GetResultAsArray($result);
        $keys[0]['expiredkeys'] = $available_keys_count;
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

function ismac() {
    $cmd = "uname";
    exec($cmd, $output);
    if ($output = "darwin") {
        return true;
    }
    return false;
}


function checkServer() {
    $isrunningmac = ismac();

    if ($isrunningmac = true) {
        $cmd = "ps aux < /dev/null | grep siriproxy";
        exec($cmd, $output, $result);
        if (count($output) >= 3) {
            return true;
        }
        
        $cmd = "ps aux < /dev/null | grep ruby";
        exec($cmd, $output2, $result);
        if (count($output2) >= 3) {
            return true;
        }
        return false;
    }
        $cmd = "ps -C siriproxy";
        exec($cmd, $output, $result);
        if (count($output) >= 2) {
            return true;
        }
        $cmd = "ps -C ruby";
        exec($cmd, $output2, $result);
        if (count($output2) >= 2) {
            return true;
        }
        return false;
}
?>
