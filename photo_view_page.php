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

//セッションの宣言
session_start();

//データベースに接続
$conn = oci_connect("photo_retrieval","********","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//前のページから渡されたデータを取得
$show_row=$_GET['show_row'];
$inquiry_type=$_GET['inquiry_type'];

//登録情報の編集ボタンを有効化しておく
$edit_display = "";

//どのリンクから来たのかによって分岐
//Topのランダムスライドから来た場合
IF($inquiry_type == 'random'){

	//前のページから渡されたデータを取得
	$in_random = $_GET['in_random'];
	//$random_got_rowを配列として初期化
	$random_got_row = array();
	//$in_randomの中身を配列で格納する
	$random_got_row=explode(',', $in_random);
	//$total_rowsに行の総数を格納
	$total_rows = count($random_got_row);

	//$sql_parts1,2,3を組み合わせて$random_sql(sql文)を作成
	$sql_parts1 = "SELECT photo_table.serial_number,TO_CHAR(filming_date,'YYYY/MM/DD'),film_number,title,favorites_level,camera_name,memo,file_pass,thumbnail_pass,secret_flug FROM photo_operation.photo_table LEFT JOIN photo_operation.camera_table ON photo_table.camera_code = camera_table.camera_code AND camera_table.user_name = '" . $_SESSION['user_name'] . "' WHERE photo_table.serial_number IN (" . $in_random . ") ORDER BY ";

	$sql_parts2 = "";
	for($i = 0; $i < $total_rows; $i++){
		$sql_parts2 = $sql_parts2 . "CASE photo_table.serial_number WHEN " . $random_got_row[$i] ." THEN 1 ELSE 2 END,";
	}
	//$sql_parts2から最後の","を消す
	$sql_parts2 = substr($sql_parts2, 0 , strlen($sql_parts2)-1);

	$_SESSION['sql'] = $sql_parts1 . $sql_parts2;

//検索から来た場合 or Topの最近のスライドから来た場合
}elseif($inquiry_type == 'retrieval' or $inquiry_type == 'top_recently' ){

	//$in_randomは検索から来た場合使用しないので空白にしておく
	$in_random = "";

	//行数をカウントするためのSQL文を作る
	$tmp_sql = "SELECT COUNT(file_pass) FROM(" . $_SESSION['sql'] . ")";

	//SQL文を実行し、実行結果を$stidに格納
	$stid = oci_parse($conn, $tmp_sql);
	oci_execute($stid);

	//実行結果の配列を$rowへ格納
	$row = oci_fetch_array($stid, OCI_NUM);

	//$total_rowsに実行結果の行数がいくつだったかを格納する
	$total_rows = $row[0];
}

//以降は経由したリンク関係なく共通の処理

//$total_rowsが1以上かどうか確認
IF($total_rows >= 1){

	//1以上の場合は画像を表示するための処理を続ける
	//画像編集ページで最後の画像の日付を変更した場合、一つ前の画像を出すように調整
	IF($show_row == $total_rows){
		$show_row = $total_rows - 1;
	}

	//一行だけ表示するSQL文を作る
	$narrow_down_sql = "SELECT * FROM(" . $_SESSION['sql'] . ") OFFSET '" . $show_row . "' ROW FETCH FIRST 1 ROW ONLY";

	//SQL文を実行し、実行結果を$stidに格納
	$stid = oci_parse($conn, $narrow_down_sql);
	oci_execute($stid);

	//実行結果の配列を$rowへ格納
	$row = oci_fetch_array($stid, OCI_NUM);

	//項目ごとの値を$rowから取得
	$serial_number = $row[0];
	$filming_date = $row[1];
	$film_number = $row[2];
	IF(isset($row[3])){
		$title = $row[3];
	}else{
		$title = "No title";
	}
	IF(isset($row[4])){
		IF($row[4] == 5){
			$favorites_level = "★★★★★";
		}elseif($row[4] == 4){
			$favorites_level = "★★★★";
		}elseif($row[4] == 3){
			$favorites_level = "★★★";
		}elseif($row[4] == 2){
			$favorites_level = "★★";
		}elseif($row[4] == 1){
			$favorites_level = "★";
		}
	}else{
		$favorites_level = "";
	}
	IF(isset($row[5])){
		$camera_name = $row[5];
	}else{
		$camera_name = "Unknown";
	}
	IF(isset($row[6])){
		$memo = $row[6];
	}else{
		$memo = "";
	}
	$file_pass = "\"" . $row[7] . "\"";
	//$row[8]に入るのはthumbnail_passだが、画像表示ページでは不要
	$secret_flug = $row[9];

	//$show_rowが0の場合(最初の写真を表示しているとき)は前ボタンを無効化
	IF($show_row == 0){
		$prev_display = "disabled";
	}else{
		$prev_display = "";
	}
	//$show_row + 1が$total_rowsの数字と同じ場合(最後の写真を表示しているとき)は次ボタンを無効化
	IF($show_row + 1 == $total_rows){
		$next_display = "disabled";
	}else{
		$next_display = "";
	}

//$total_rowsが0の場合は以下
//登録情報を編集して条件に合致するものが無くなった場合にこの処理に行く
}else{
	//表示する画像をエラー時のものにする
	$file_pass = "no_image.jpg";
	//表示する項目を全て空白にする
	$title = "";
	$filming_date = "";
	$film_number = "";
	$favorites_level = "";
	$camera_name = "";
	$secret_flug = 0;
	$memo = "";

	//前ボタンを無効化
	$prev_display = "disabled";

	//次ボタンを無効化
	$next_display = "disabled";

	//登録情報の編集ボタンを無効化
	$edit_display = "disabled";
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

<!-- ------------------ 表示ページ開始 ------------------ -->
<div id="display">
<div class="title_field">
	<p class="photo_title"><?php print $title;?></p>
	<p class="stars"><?php print $favorites_level;?></p>
</div>
<div class="photo_view"><img src=<?php print $file_pass;?>></div>
<div class="button_field">
	<form class="prev_button" name="prev_button" method="GET" action="#">
		<input type="submit" value="前へ" <?php echo $prev_display;?>>
		<input type="hidden" name="show_row" value=<?php print $show_row - 1;?>>
		<input type="hidden" name="in_random" value=<?php print $in_random;?>>
		<input type="hidden" name="inquiry_type" value=<?php print $inquiry_type;?>>
	</form>
	<form class="slide_button" name="slide_button" method="GET" action="#">
		<input type="button" value="スライドショー" name="">
	</form>
	<form class="next_button" name="next_button" method="GET" action="#">
		<input type="submit" value="次へ" <?php echo $next_display;?>>
		<input type="hidden" name="show_row" value=<?php print $show_row + 1;?>>
		<input type="hidden" name="in_random" value=<?php print $in_random;?>>
		<input type="hidden" name="inquiry_type" value=<?php print $inquiry_type;?>>
	</form>
</div>

<dl class="photo_data">
	<dt>タイトル:
		<dd><?php print $title;?></dd>
	</dt>
	<dt>撮影日:
		<dd><?php print $filming_date;?></dd>
	</dt>
	<dt>番号:
		<dd><?php print $film_number;?></dd>
	</dt>
	<dt>お気に入り:
		<dd><?php print $favorites_level;?></dd>
	</dt>
	<dt>撮影カメラ:
		<dd><?php print $camera_name;?></dd>
	</dt>
	<?php 
	IF($secret_flug == 1){
		echo "\t<dt>非表示フラグ:\n\t\t<dd>ON</dd>\n\t</dt>";
	}else{
	}
	?>
	<dt>メモ:
		<dd><?php print $memo;?></dd>
	</dt>
</dl>

<div class="button_field">
	<form class="editing_button" name="editing_button" method="GET" action="editing_page.php">
		<input type="submit" value="登録情報の編集" <?php echo $edit_display;?>>
		<input type="hidden" name="show_row" value=<?php print $show_row;?>>
	</form>

	<form class="back_button" name="back_button" method="GET" action="<?php
	//経由したページによって戻るページを分岐
	IF($inquiry_type == 'random' or $inquiry_type == 'top_recently'){
		print "top_page.php";
	}elseif($inquiry_type == 'retrieval'){
		print "search_result_page.php";
	}
	?>">
		<input type="submit" value="戻る">
		<input type="hidden" name="show_row" value="<?php print $show_row;?>">
		<input type="hidden" name="via_page" value="display_page">
	</form>
</div>

<hr>

</div>
<!-- ------------------ 表示ページ終了 ------------------ -->

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