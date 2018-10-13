<?php

//セッションの宣言
session_start();

$slide_speed = $_POST['slide_speed'];
$secret_status = $_POST['secret_status'];
$now_password = $_POST['now_password'];
$new_password = $_POST['new_password'];
$pass_change_flug = $_POST['pass_change_flug'];

//$secret_statusはtrue,falseで値をもらっているのでvisible,hiddenに変える
IF($secret_status == "true"){
	$secret_status = "visible";
}elseif($secret_status == "false"){
	$secret_status = "hidden";
}

//データベースに接続
$conn = oci_connect("photo_retrieval","********","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//パスワードを変更するかどうかで分岐(updateの文が変わる)
IF($pass_change_flug == "true"){
	//フラグがtrueの場合は現在のパスワードを確認する為の問い合わせを行う
	//sql文の作成
	$sql = "SELECT password FROM photo_operation.user_table WHERE user_name = '" . $_SESSION['user_name'] . "'";

	//SQL文を実行し、実行結果を$stidに格納
	$stid = oci_parse($conn, $sql);
	oci_execute($stid);

	//実行結果の配列を$rowへ格納
	$row = oci_fetch_array($stid, OCI_NUM);

	//現在のパスワードとフォームで入力されたパスワードが一致しているか確認する
	IF($row[0] <> $now_password){
		//一致しなかった場合はここで処理終了
		//戻り値として"password_mismatch"と返す
		exit("password_mismatch");

	}else{
		//一致した場合は処理を続行
		$sql_parts = "password = '" . $new_password . "', ";
	}
}else{
	//pass_change_flugがfalse(パスワードを変更しない)の場合は以下
	$sql_parts = "";

}

//sql文の作成
$sql = "UPDATE user_table SET " . $sql_parts . "slide_speed = '" . $slide_speed . "', secret_status = '" . $secret_status . "' WHERE user_name = '" . $_SESSION['user_name'] . "'";

//操作用のユーザでデータベースに接続
$conn = oci_connect("photo_operation","********","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//SQL文を実行し、実行結果を$stidに格納
$stid = oci_parse($conn, $sql);
oci_execute($stid);

//戻り値として"success"と返して処理終了
echo "success";

?>