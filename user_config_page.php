<!DOCTYPE HTML>
<html lang="ja">
<head>

<title>My Photo Library</title>
<meta name="viewport" content="width=750" >
<link rel="stylesheet" type="text/css" href="photo.css">
<link rel="stylesheet" href="slick-1.8.1/slick/slick.css">
<link rel="stylesheet" href="slick-1.8.1/slick/slick-theme.css">

<script src="jquery-3.3.1.min.js"></script>
<script src="slick-1.8.1/slick/slick.js"></script>

<?php
//エラーがあった場合、画面に表示する。
ini_set("display_errors", 1);
error_reporting(E_ALL);

//データベースに接続
$conn = oci_connect("photo_retrieval","********","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//セッションの宣言
session_start();

//ユーザ情報を取得
$user_sql = "SELECT user_name,password,slide_speed,secret_status FROM photo_operation.user_table WHERE user_name = '" . $_SESSION['user_name'] . "'";

//SQL文を実行し、実行結果を$stidに格納
$stid = oci_parse($conn, $user_sql);
oci_execute($stid);

//実行結果の配列を$rowへ格納
$row = oci_fetch_array($stid, OCI_NUM);

//ユーザ情報を変数に入れる
$password = $row[1];
$slide_speed = $row[2];
$secret_status = $row[3];

//スライドショーのスピードを決めるプルダウンのデフォルト値を設定
//空の変数は空白の宣言をしておく
IF($slide_speed == "slow"){
	$slide_option1 = " selected";
	$slide_option2 = "";
	$slide_option3 = "";
}elseif($slide_speed == "normal"){
	$slide_option1 = "";
	$slide_option2 = " selected";
	$slide_option3 = ""; 
}elseif($slide_speed == "high"){
	$slide_option1 = "";
	$slide_option2 = "";
	$slide_option3 = " selected";
}

//非表示設定のチェックボックスのデフォルト値を設定
IF($secret_status == "visible"){
	$secret_checkbox = ' checked="checked"';
}elseif($secret_status == "hidden"){
	$secret_checkbox = "";
}

?>

</head>
<body>

<!-- --------------------コンテナ開始-------------------- -->
<div id="container">

<!-- ---------------------ページ開始--------------------- -->
<div id="page">

<!-- --------------------タイトル開始-------------------- -->
<div id="title">
	<h1>My Photo Library</h1>
	<p>ようこそ、<?php print $_SESSION['user_name']; ?>様</p>
</div>
<!-- --------------------タイトル終了-------------------- -->

<!-- ----------------- ユーザ設定ページ ----------------- -->
<div id="user_conf">
<p>ユーザ設定</p>
<form class="user_conf_form" method="post" name="user_conf_form">
	<dl>
		<dt>スライドショーのスピード:
			<dd>
				<select class="slide_speed_select" name="slide_speed">
				<option value="slow"<?php echo $slide_option1 ?>>ゆっくり</option>
				<option value="normal"<?php echo $slide_option2 ?>>ふつう</option>
				<option value="high"<?php echo $slide_option3 ?>>早め</option>
				</select>
			</dd>
		</dt>
		<dt>非表示設定の写真を表示する:
			<dd>
				<input class="secret_status_check" type="checkbox" name="secret_status" value="enable"<?php echo $secret_checkbox ?>>
			</dd>
		</dt>
		<dt>パスワードの変更:
			<dd>
				<input class="now_password_input" type="password" name="now_password" placeholder="現在のパスワード" maxlength='10'>
			</dd>
		</dt>
		<dt class="password_mismatch">
			<dd>
				<input class="new_password_input" type="password" name="new_password" placeholder="新しいパスワード" maxlength='10'>
			</dd>
		</dt>
		<dt class="cant_use_pass">
			<dd>
				<input class="password_agein_input" type="password" name="password_agein" placeholder="再入力" maxlength='10'>
			</dd>
		</dt>
		<dt>
			<dd>
				<button class="user_setting_submit" type="button">保存</button>
				<button type="button" onclick="history.back()">キャンセル</button>
			</dd>
		</dt>
	</dl>
</form>

<script src="user_setting_change.js"></script>

</div>

<!-- --------------------フッター開始-------------------- -->
<div id="footer">
	<p><span>Copyright © Kouhei Ichikawa All Rights Reserved.</span></p>
</div>
<!-- --------------------フッター終了-------------------- -->

</div>
<!-- ---------------------ページ終了--------------------- -->

</div>
<!-- --------------------コンテナ終了-------------------- -->

</body>
</html>