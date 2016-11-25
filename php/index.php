<?php
require_once("mysql.class.php");
$sql = "show TABLES";
$dosql->Execute($sql);
$arr= array();
$start = urlencode('<?php');
while($row = $dosql->GetArray()){
    $table = $row['Tables_in_'.$db_name];
echo <<<Eof
    <a href="table.php?table={$table}" target="_blank">{$table}</a><br>
Eof;

}
?>