<?php

//セッションの宣言
session_start();

$date = $_POST['name1'];

//データベースに接続
$conn = oci_connect("photo_retrieval","mS6EqirX","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//シークレットフラグが隠す設定だった場合、SQL文でフラグONの写真がヒットしないようにする
IF($_SESSION['secret_status'] == 'hidden'){
	$secret_flug = ' AND secret_flug = 0';
}else{
	$secret_flug = '';
}

//sql文の作成
$sql = "SELECT COUNT(file_pass) FROM(SELECT * FROM photo_operation.photo_table WHERE filming_date = '" . $date . "' AND user_name = '" . $_SESSION['user_name'] . "'" . $secret_flug . ")";

//SQL文を実行し、実行結果を$stidに格納
$stid = oci_parse($conn, $sql);
oci_execute($stid);

//実行結果の配列を$rowへ格納
$row = oci_fetch_array($stid, OCI_NUM);

//結果を表示(戻り値としてjavascriptへ返す)
echo $row[0];

?>