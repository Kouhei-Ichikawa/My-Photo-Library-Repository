<?php
//アップロードページで選択した日付にある写真の枚数をカウントする処理

//エラーがあった場合、画面に表示する。
ini_set("display_errors", 1);
error_reporting(E_ALL);

//セッションの宣言
session_start();

//データベースに接続
$conn = oci_connect("photo_retrieval","mS6EqirX","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//javascriptからもらった値を取得
$century_value = $_POST['century_value'];
$year_value = $_POST['year_value'];
$month_value = $_POST['month_value'];
$day_value = $_POST['day_value'];

//sql文を作成
$sql = "SELECT COUNT(serial_number) FROM photo_operation.photo_table WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $century_value . $year_value . "/" . $month_value . "/" . $day_value . "')";

//SQL文を実行し、実行結果を$stidに格納
$stid = oci_parse($conn, $sql);
oci_execute($stid);
//実行結果の配列を$rowへ格納
$row = oci_fetch_array($stid, OCI_NUM);

//問い合わせた結果をjavascriptに返す
echo $row[0];
?>