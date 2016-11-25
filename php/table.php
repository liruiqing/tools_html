<?php

require_once("mysql.class.php");
//配置要连接的数据表
$table = $_GET['table'];

header('Content-type: text/html; charset=utf8');
$sql = "show columns from $table";
$dosql->Execute($sql);
$arr= array();

$li = '';


while($row = $dosql->GetArray()){
    if($row['Field']!='id'){
        $arr[] = $row['Field'];
        $li .= '$'.$row['Field'].'= isset($_GET["'.$row['Field'].'"]) ? $_GET["'.$row['Field'].'"]:"";<br>';
    }
}


echo '//---------------------------------------------- start ----------------------------------------------------<br>';
echo 'require_once("mysql.class.php");<br>';

echo '$action = $_GET["action"];<br>
    if($action == null){<br>
        echo "error";<br>
        exit;<br>
    }';

echo '<br>';
echo '//---------------------------------------------- insert ----------------------------------------------------<br>';
echo 'if($action == "insert_'.$table.'") {<br>
    '.$li.'
    $nowTime  = date("Y-m-d H:i:s", time());<br>
    $sql="insert into '.$table.'('.getInsertFront($arr).') VALUES ('.getInsertLast($arr).')";<br>
    $r = $dosql->ExecNoneQuery($sql);<br>
   if($r){<br>
       echo "success";<br>
       exit;<br>
   }<br>
}<br>';


echo '//---------------------------------------------- update ----------------------------------------------------<br>';
echo 'if($action == "update_'.$table.'") {<br>
     $id = isset($_GET["id"]) ? $_GET["id"]:"";<br>
    '.$li.'
     $nowTime  = date("Y-m-d H:i:s", time());<br>
    $sql="update '.$table.' set '.getUpdate($arr).' where id=\'$id\'";<br>
    $r = $dosql->ExecNoneQuery($sql);<br>
   if($r){<br>
       echo "success";<br>
       exit;<br>
   }<br>
}<br>';
echo '//---------------------------------------------- delete ----------------------------------------------------<br>';
echo 'if($action == "delete_'.$table.'") {<br>
    $id = isset($_GET["id"]) ? $_GET["id"]:"";<br>
    $sql="delete from '.$table.' where  id=\'$id\'";<br>
    $r = $dosql->ExecNoneQuery($sql);<br>
   if($r){<br>
       echo "success";<br>
       exit;<br>
   }<br>
}<br>';
echo '//---------------------------------------------- select ----------------------------------------------------<br>';
echo 'if($action == "select_'.$table.'") {<br>
    $id = isset($_GET["id"]) ? $_GET["id"]:"";<br>
    $sql="select * from '.$table.' where  id=\'$id\'";<br>
    $r = $dosql->GetOne($sql);<br>
   echo json_encode($r);<br>
}<br>';
echo '//---------------------------------------------- end ----------------------------------------------------<br>';


echo '<br>';
echo '<br>';
echo '<br>';
echo getInsertFront($arr);
function  getInsertFront($arr){
   $s = implode(',',$arr);
    return $s;
}
echo '<hr>';

echo getInsertLast($arr);
function getInsertLast($arr){
    $s = implode('\',\'$',$arr);
    $s = '\'$'.$s.'\'';
    return $s;
}
echo '<hr>';
echo getUpdate($arr);
function getUpdate($arr){
    $arrInner = array();
    foreach($arr as $key){
        $arrInner[] ="$key='$".$key."'" ;
    }
    return (implode(',',$arrInner));
}
echo '<hr>';
echo getFrontJson($arr);
function getFrontJson($arr){
    $arrInner = array();
    foreach($arr as $key){
        $arrInner[] ="$key:''" ;
    }
    return ('{'.implode(',',$arrInner).'}');
}
echo '<hr>';
echo getFrontJson1($arr);
function getFrontJson1($arr){
    $arrInner = array();
    foreach($arr as $key){
        $arrInner[] =$key.':'."\<\?php echo \$row['".$key."']\?\>".'"' ;
    }
    return str_replace('','',('{'.implode(',',$arrInner).'}'));
}
echo '<hr>';

echo getFrontSelect($table);
function getFrontSelect($table){
    $li = '<\?php<br>';
    $li .='$sql = "select * from '.$table.' where id=\'$id\'";<br>';
    $li .='$dosql->Execute($sql);<br>';
    $li .='$li = "";<br>';
    $li .='$index=0;<br>';
    $li .='while($row= $dosql->GetArray()){<br>';
    $li .='$index++;<br>';
    $li .='$li .= \'\';<br>';
    $li .='};<br>';
    $li .='echo $li;<br>';
    $li .='\?><br>';
    return $li;
}
echo '<hr>';
getFrontRow($arr);
function getFrontRow($arr){

    foreach($arr as $key){
        echo '$'.$key.' = $row["'.$key.'"];<br>';
    }

}
echo '<hr>';

foreach($arr as $value){
    echo '//---------------------------------------------- '.$value.'  start ----------------------------------------------------<br>';
    echo 'if($action == "update_'.$value.'_'.$table.'") {<br>
    $id = isset($_GET["id"]) ? $_GET["id"]:"";<br>
    $nowTime  = date("Y-m-d H:i:s", time());<br>
    $'.$value.' = isset($_GET["'.$value.'"]) ? $_GET["'.$value.'"]:"";<br>
    $sql="update '.$table.' set '.$value.'=\'$'.$value.'\' where id=\'$id\'";<br>
    $r = $dosql->GetOne($sql);<br>
   echo json_encode(array(state=>\'success\'));<br>
}<br>';
    echo '//----------------------------------------------  '.$value.'  end ----------------------------------------------------<br>';
}


?>