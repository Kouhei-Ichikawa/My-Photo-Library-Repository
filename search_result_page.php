<!DOCTYPE HTML>
<html lang="ja">
<head>

<title>My Photo Library</title>
<meta name="viewport" content="width=750" >
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="photo.css">
<link rel="stylesheet" href="slick-1.8.1/slick/slick.css">
<link rel="stylesheet" href="slick-1.8.1/slick/slick-theme.css">

<script src="jquery-3.3.1.min.js"></script>
<script src="slick-1.8.1/slick/slick.js"></script>
<script src="last_day_func.js"></script>

<script>
//詳細検索フォームで無効な日付を回避するためのスクリプト
last_day_func('till');
});
</script>

<?php
//エラーがあった場合、画面に表示する。
ini_set("display_errors", 1);
error_reporting(E_ALL);

//セッションの宣言
session_start();

//どのページから来たかの変数を取得
$via_page = $_GET['via_page'];

//経由したページごとに分岐
//1.日付検索から来た場合
IF($via_page == "calendar"){

	//渡された変数の値を取得
	$filming_date = $_GET['filming_date'];

	//$show_row(SQLの結果の何行目を表示させるか指定する変数)のスタート値を設定
	$show_row = 0;

	//$page_num(表示するページ番号)のスタート値を設定
	$page_num = 0;

	//パーツを5つ組み合わせてSQL文を作成
	$sql_parts1 = "SELECT photo_table.serial_number,TO_CHAR(filming_date,'YYYY/MM/DD'),film_number,title,favorites_level,camera_name,memo,file_pass,thumbnail_pass,secret_flug
 FROM photo_operation.photo_table LEFT JOIN photo_operation.camera_table ON photo_table.camera_code = camera_table.camera_code AND camera_table.user_name = '" . $_SESSION['user_name'] . "' ";

	$sql_parts2 = "WHERE filming_date = '" . $filming_date . "' ";

	$sql_parts3 = "AND photo_table.user_name = '" . $_SESSION['user_name'] . "' ";

	IF($_SESSION['secret_status'] == 'hidden'){
		$sql_parts4 = "AND secret_flug = 0 ";
	}else{
		$sql_parts4 = "";
	}

	$sql_parts5 = 'ORDER BY film_number ASC';

	//SQL文をセッション変数に格納する
	$_SESSION['sql'] = $sql_parts1 . $sql_parts2 . $sql_parts3 . $sql_parts4 . $sql_parts5;

//2.お気に入り検索から来た場合
}elseif($via_page == 'favorite'){

	//渡された変数の値を取得
	//star_1～5はチェックボックスからの値。チェックされなかった場合はNULLか未定義になるので宣言しておく
	if (isset($_GET['star_1'])) {
		$star_1 = $_GET['star_1'];
	}else{$star_1 = "";
	}
	if (isset($_GET['star_2'])) {
		$star_2 = $_GET['star_2'];
	}else{$star_2 = "";
	}
	if (isset($_GET['star_3'])) {
		$star_3 = $_GET['star_3'];
	}else{$star_3 = "";
	}
	if (isset($_GET['star_4'])) {
		$star_4 = $_GET['star_4'];
	}else{$star_4 = "";
	}
	if (isset($_GET['star_5'])) {
		$star_5 = $_GET['star_5'];
	}else{$star_5 = "";
	}
	$stars_sort = $_GET['stars_sort'];

	//$show_row(SQLの結果の何行目を表示させるか指定する変数)のスタート値を設定
	$show_row = 0;

	//$page_num(表示するページ番号)のスタート値を設定
	$page_num = 0;

	//パーツを5つ組み合わせてSQL文を作成
	$sql_parts1 = "SELECT photo_table.serial_number,TO_CHAR(filming_date,'YYYY/MM/DD'),film_number,title,favorites_level,camera_name,memo,file_pass,thumbnail_pass,secret_flug
 FROM photo_operation.photo_table LEFT JOIN photo_operation.camera_table ON photo_table.camera_code = camera_table.camera_code AND camera_table.user_name = '" . $_SESSION['user_name'] . "'";

	$sql_parts2 = 'WHERE (';
	IF($star_1 == "enable"){$sql_parts2 = $sql_parts2 . 'favorites_level = 1 OR ';}
	IF($star_2 == "enable"){$sql_parts2 = $sql_parts2 . 'favorites_level = 2 OR ';}
	IF($star_3 == "enable"){$sql_parts2 = $sql_parts2 . 'favorites_level = 3 OR ';}
	IF($star_4 == "enable"){$sql_parts2 = $sql_parts2 . 'favorites_level = 4 OR ';}
	IF($star_5 == "enable"){$sql_parts2 = $sql_parts2 . 'favorites_level = 5 OR ';}
	//末尾の"OR "を削除
	$sql_parts2 = substr($sql_parts2 , 0 , strlen($sql_parts2)-3);
	$sql_parts2 = $sql_parts2 . ") AND ";

	$sql_parts3 = "photo_table.user_name = '" . $_SESSION['user_name'] . "' ";

	IF($_SESSION['secret_status'] == 'hidden'){
		$sql_parts4 = "AND secret_flug = 0 ";
	}else{
		$sql_parts4 = "";
	}

	$sql_parts5 = 'ORDER BY ';
	IF($stars_sort == 'no_sort'){
		$sql_parts5 = $sql_parts5 . 'filming_date DESC , film_number ASC';
	}elseif($stars_sort == "Many_first"){
		$sql_parts5 = $sql_parts5 . 'favorites_level DESC , filming_date DESC , film_number ASC';
	}elseif($stars_sort == "Small_first"){
		$sql_parts5 = $sql_parts5 . 'favorites_level ASC , filming_date DESC , film_number ASC';
	}

	//SQL文をセッション変数に格納する
	$_SESSION['sql'] = $sql_parts1 . $sql_parts2 . $sql_parts3 . $sql_parts4 . $sql_parts5;

//3.詳細検索から来た場合
}elseif($via_page == 'detailed'){

	//前のページから渡されたデータを取得
	$from_century = $_GET['from_century'];
	$from_year = $_GET['from_year'];
	$from_month = $_GET['from_month'];
	$from_day = $_GET['from_day'];
	$till_century = $_GET['till_century'];
	$till_year = $_GET['till_year'];
	$till_month = $_GET['till_month'];
	$till_day = $_GET['till_day'];
	$days_sort = $_GET['days_sort'];
	//star_1～5はチェックボックスからの値。チェックされなかった場合はNULLか未定義になるので宣言しておく
	if (isset($_GET['star_1'])) {
		$star_1 = $_GET['star_1'];
	}else{$star_1 = "";
	}
	if (isset($_GET['star_2'])) {
		$star_2 = $_GET['star_2'];
	}else{$star_2 = "";
	}
	if (isset($_GET['star_3'])) {
		$star_3 = $_GET['star_3'];
	}else{$star_3 = "";
	}
	if (isset($_GET['star_4'])) {
		$star_4 = $_GET['star_4'];
	}else{$star_4 = "";
	}
	if (isset($_GET['star_5'])) {
		$star_5 = $_GET['star_5'];
	}else{$star_5 = "";
	}
	$stars_sort = $_GET['stars_sort'];
	$sort_sort = $_GET['sort_sort'];
	$camera_code = $_GET['camera_code'];
	$key_word = $_GET['key_word'];

	//$show_row(SQLの結果の何行目を表示させるか指定する変数)のスタート値を設定
	$show_row = 0;

	//$page_num(表示するページ番号)のスタート値を設定
	$page_num = 0;

	//パーツを組み合わせてSQL文を作成
	$sql_parts1 = "SELECT photo_table.serial_number,TO_CHAR(filming_date,'YYYY/MM/DD'),film_number,title,favorites_level,camera_name,memo,file_pass,thumbnail_pass,secret_flug FROM photo_operation.photo_table LEFT JOIN photo_operation.camera_table ON photo_table.camera_code = camera_table.camera_code AND camera_table.user_name = '" . $_SESSION['user_name'] . "'";
	
	$sql_parts2 = "WHERE filming_date BETWEEN TO_DATE('" . $from_century . $from_year . "/" . $from_month . "/" . $from_day . "') AND TO_DATE('" . $till_century . $till_year . "/" . $till_month . "/" . $till_day . "') AND ";

	$sql_parts3 = "(";
	IF($star_1 == 'enable'){$sql_parts3 = $sql_parts3 . 'favorites_level = 1 OR ';}
	IF($star_2 == "enable"){$sql_parts3 = $sql_parts3 . 'favorites_level = 2 OR ';}
	IF($star_3 == "enable"){$sql_parts3 = $sql_parts3 . 'favorites_level = 3 OR ';}
	IF($star_4 == "enable"){$sql_parts3 = $sql_parts3 . 'favorites_level = 4 OR ';}
	IF($star_5 == "enable"){$sql_parts3 = $sql_parts3 . 'favorites_level = 5 OR ';}
	//末尾の"OR "を削除
	$sql_parts3 = substr($sql_parts3 , 0 , strlen($sql_parts3)-3);
	$sql_parts3 = $sql_parts3 . ") AND ";
	
	IF($camera_code == ""){
		$sql_parts4 = "";
	}else{
		$sql_parts4 = "photo_table.camera_code = '" . $camera_code . "' AND ";
	}

	IF($key_word == ""){
		$sql_parts5 = "";
	}else{
		$sql_parts5 = "(title LIKE '%" . $key_word . "%' OR memo LIKE '%" . $key_word . "%') AND ";
	}

	$sql_parts6 = "photo_table.user_name = '" . $_SESSION['user_name'] . "' ";

	IF($_SESSION['secret_status'] == 'hidden'){
		$sql_parts7 = "AND secret_flug = 0 ";
	}else{
		$sql_parts7 = "";
	}

	IF($days_sort == "new_first"){
		$day_parts = "filming_date DESC , ";
	}elseif($days_sort == "old_first"){
		$day_parts = "filming_date ASC , ";
	}

	IF($stars_sort == "no_sort"){
		$star_parts = "";
	}elseif($stars_sort == "Many_first"){
		$star_parts = "favorites_level DESC , ";
	}elseif($stars_sort == "Small_first"){
		$star_parts = "favorites_level ASC , ";
	}

	IF($sort_sort == "days_first"){
		$sql_parts8 = "ORDER BY " . $day_parts . $star_parts . "film_number ASC";
	}elseif($sort_sort == "starts_first"){
		$sql_parts8 = "ORDER BY " . $star_parts . $day_parts . "film_number ASC";
	}

	//SQL文をセッション変数に格納する
	$_SESSION['sql'] = $sql_parts1 . $sql_parts2 . $sql_parts3 . $sql_parts4 . $sql_parts5 . $sql_parts6 . $sql_parts7 . $sql_parts8;

//4.検索結果のページ移動ボタンで来た場合
}elseif($via_page == 'page_change'){

	//$page_num(表示するページ番号)を取得
	$page_num = $_GET['page_num'];

	//$show_row(SQLの結果の何行目を表示させるか指定する変数)の値を設定
	$show_row = $page_num * 12;

//5.画像表示ページの戻るボタンで来た場合
}elseif($via_page == 'display_page'){

	//画像表示ページから変数をもらう
	$show_row = $_GET['show_row'];

	//$page_num(表示するページ番号)を$show_rowから割り出す
	$page_num = floor($show_row / 12);

	//$show_row(SQLの結果の何行目を表示させるか指定する変数)の値を設定
	$show_row = $page_num * 12;
}

//以降は共通の処理

//そのページで最初に表示する写真の番号を計算する
IF($page_num == 0){ 
	$offset_number = 0;
}else{
	$offset_number = $page_num * 12;
}

//データベースに接続
$conn = oci_connect("photo_retrieval","********","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//行数をカウントするためのSQL文を作る
$tmp_sql = "SELECT COUNT(file_pass) FROM(" . $_SESSION['sql'] . ")";

//SQL文を実行し、実行結果を$stidに格納
$stid = oci_parse($conn, $tmp_sql);
oci_execute($stid);

//実行結果の配列を$rowへ格納
$row = oci_fetch_array($stid, OCI_NUM);

//$total_rowsに実行結果の行数がいくつだったかを格納する
$total_rows = $row[0];

//$total_rowsを元に総ページ数を割り出す
IF($total_rows < 12){
	$total_pages = 0;
}else{
	$total_pages = ceil( $total_rows / 12 - 1 );
}

//分岐ごとに作成したsql文を1ページ12枚表示するようにネストする
$this_page_sql = "SELECT * FROM(" . $_SESSION['sql'] . ") OFFSET '" . $offset_number . "' ROW FETCH FIRST 12 ROW ONLY";

//画面に表示する用のページ番号の変数を作っておく
$this_page_name = $page_num + 1;
$total_page_name = $total_pages + 1;


//以下から撮影日のプルダウンメニューの初期値の指定
//$filming_dateを元に変数を作成
//今日の日付を元に変数を作成
$today = date("Y/m/d");
$array_now_date = array();
$array_now_date = explode("/",$today);

//以下から世紀の箇所のデフォルト値を設定する処理
$tmp_century = substr($array_now_date[0] ,0 ,2 ); 
IF($tmp_century == 19){
	//19世紀のプルダウンメニューがデフォルトになる
	$now_century = array(""," selected","");
}else{
	//20世紀のプルダウンメニューがデフォルトになる
	$now_century = array("",""," selected");
}

//以下から年代の箇所のデフォルト値を設定する処理
$tmp_year = substr($array_now_date[0] ,2 ,2 );
$now_year = array();
//$filming_yearに空白の要素を100個作る(0~99まで)
for($i=0 ; $i<100 ; $i++){
	$now_year[] = "";
}
//年代の数字と同じ番号の配列がデフォルトになる
$now_year[$tmp_year] = " selected";

//以下から月の箇所のデフォルト値を設定する処理
//$filming_dateから作成した配列の値が文字列と認識されることがあるのでintvalで数値型に変換
$tmp_month = intval($array_now_date[1]);
$now_month = array("");
//$filming_monthに空白の要素を12個作る
for($i=1 ; $i<13 ; $i++){
	$now_month[] = "";
}
//月の数字と同じ番号の配列がデフォルトになる
$now_month[$tmp_month] = " selected";

//以下から日の箇所のデフォルト値を設定する処理
//$filming_dateから作成した配列の値が文字列と認識されることがあるのでintvalで数値型に変換
$tmp_day = intval($array_now_date[2]);
$now_day = array("");
//$filming_dayに空白の要素を31個作る
for($i=1 ; $i<32 ; $i++){
	$now_day[] = "";
}
//日の数字と同じ番号の配列がデフォルトになる
$now_day[$tmp_day] = " selected";

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

<!-- --------------------検索結果開始-------------------- -->
<div id="search_page">
<p>検索結果 (<?php print $total_rows . "枚 " . $this_page_name . "/" . $total_page_name; ?>ページ)</p>
<form class="slide_button">
	<input type="button" value="スライドショー" name="">
</form>

<div id="search_results">

<?php

//SQL文を実行し、実行結果を$stidに格納
$stid = oci_parse($conn, $this_page_sql);
oci_execute($stid);

while ($row = oci_fetch_array($stid, OCI_NUM)) {
	foreach ($row as $item) {
		if($row[8] == $item){
			//サムネイルを拾ったらリンクの記述をする
			echo "\t<div class=\"back_ground\">\n\t\t<form name=\"photo_form" . $show_row . "\" method=\"GET\" action=\"photo_view_page.php\">\n\t\t\t<input type=\"image\" src=\"" . $item . "\" alt=\"image\">\n\t\t\t<input type=\"hidden\" name=\"show_row\" value=" . $show_row . ">\n\t\t\t<input type=\"hidden\" name=\"inquiry_type\" value=\"retrieval\">\n\t\t</form>\n\t</div>\n";

			//3つごとに記述に<br>をはさむ(条件は3で割って余りが2の行であるか)
			if($show_row % 3 == 2){echo "\t<br>\n";}

			//$show_rowに+1($show_rowのスタートの値は元来たページにより変わる)
			$show_row = $show_row + 1;
		}
	}
}

//以下からカメラ名のプルダウンの作成とデフォルト値を設定する処理
//プルダウンメニューの記述を格納する変数を宣言
//中身の例 → <option value="カメラコード">カメラ名</option>
$make_camera_name_option = "<option value=\"\">-</option>\n";

//カメラ名を辞書順に取得するSQL文を作成
$camera_name_sql = "SELECT camera_code,camera_name FROM photo_operation.camera_table WHERE user_name = '" . $_SESSION['user_name'] . "' ORDER BY NLSSORT(camera_name,'NLS_SORT=Japanese')";

//SQL文を実行し、実行結果を$stidに格納
$stid = oci_parse($conn, $camera_name_sql);
oci_execute($stid);

//プルダウンメニューの記述を変数に入れていく
while ($row = oci_fetch_array($stid, OCI_NUM)) {
	foreach ($row as $item) {
		if($row[0] == $item){
			//カメラコードを変数に格納
			$make_camera_name_option = $make_camera_name_option . "\t\t\t    <option value=\"" . $item . "\"";
		}else{
			//カメラ名を変数に格納
			$make_camera_name_option = $make_camera_name_option . ">" . $item . "</option>\n";
		}
	}
}
?>

</div>

<form class="back_button" action="top_page.php">
	<input type="submit" value="戻る">
</form>

<div id="page_change">
<?php
//前へボタンの要素の記述
//現在のページ-1を表示する(現在のページが0の場合は無効化)
$tmp_num = $page_num - 1;
IF($page_num > 0){
	echo "\t<form name=\"prev_page\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\"><</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}else{
	echo "\t<form name=\"prev_page\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\" disabled><</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

//-4ページ移動ボタンの要素の記述
//現在のページが最後のページの場合は表示(ただし$page_num - 4 < 0になる場合は非表示)
$tmp_num = $page_num - 4;
$tmp_name = $this_page_name - 4;
IF($page_num == $total_pages and $page_num - 4 >= 0){
	echo "\t<form name=\"page4_prev\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\">" . $tmp_name . "</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

//-3ページ移動ボタンの要素の記述
//現在のページが最後のページ-1以上の場合は表示(ただし$page_num - 3 < 0になる場合は非表示)
$tmp_num = $page_num - 3;
$tmp_name = $this_page_name - 3;
IF($page_num >= $total_pages - 1 and $page_num - 3 >= 0){
	echo "\t<form name=\"page3_prev\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\">" . $tmp_name . "</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

//-2ページ移動ボタンの要素の記述
//現在のページが最初のページ+2以上の場合は表示
$tmp_num = $page_num - 2;
$tmp_name = $this_page_name - 2;
IF($page_num >= 2){
	echo "\t<form name=\"page2_prev\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\">" . $tmp_name . "</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

//-1ページ移動ボタンの要素の記述
//現在のページが最初のページ+1以上の場合は表示
$tmp_num = $page_num - 1;
$tmp_name = $this_page_name - 1;
IF($page_num >= 1){
	echo "\t<form name=\"page1_prev\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\">" . $tmp_name . "</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

//0ページ移動ボタンの要素の記述
//常に表示
echo "\t<form name=\"this_page\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button class=\"now_page\" type=\"submit\">" . $this_page_name . "</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $page_num . "\">\n\t</form>\n";

//+1ページ移動ボタンの要素の記述
//現在のページが最後のページ-1以下の場合は表示
$tmp_num = $page_num + 1;
$tmp_name = $this_page_name + 1;
IF($page_num <= $total_pages - 1){
	echo "\t<form name=\"page1_next\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\">" . $tmp_name . "</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

//+2ページ移動ボタンの要素の記述
//現在のページが最後のページ-2以下の場合は表示
$tmp_num = $page_num + 2;
$tmp_name = $this_page_name + 2;
IF($page_num <= $total_pages - 2){
	echo "\t<form name=\"page2_next\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\">" . $tmp_name . "</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

//+3ページ移動ボタンの要素の記述
//現在のページが最初のページ+1以下の場合は表示(ただし$page_num + 3 > $total_pagesになる場合は非表示)
$tmp_num = $page_num + 3;
$tmp_name = $this_page_name + 3;
IF($page_num <= 1 and $page_num + 3 <= $total_pages){
	echo "\t<form name=\"page3_next\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\">" . $tmp_name . "</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

//+4ページ移動ボタンの要素の記述
//現在のページが最初のページの場合は表示(ただし$page_num + 4 > $total_pagesになる場合は非表示)
$tmp_num = $page_num + 4;
$tmp_name = $this_page_name + 4;
IF($page_num == 0 and $page_num + 4 <= $total_pages){
	echo "\t<form name=\"page4_next\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\">" . $tmp_name . "</button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

//次へボタンの要素の記述
//現在のページ+1を表示する(現在のページが最後のページの場合は無効化)
$tmp_num = $page_num + 1;
IF( ! ($page_num == $total_pages) ){
	echo "\t<form name=\"next_page\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\">></button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}else{
	echo "\t<form name=\"next_page\" method=\"GET\" action=\"search_result_page.php\">\n\t\t<button type=\"submit\" disabled>></button>\n\t\t<input type=\"hidden\" name=\"via_page\" value=\"page_change\">\n\t\t<input type=\"hidden\" name=\"page_num\" value=\"" . $tmp_num . "\">\n\t</form>\n";
}

?>
</div>

</div>
<!-- --------------------検索結果終了-------------------- -->

<!-- --------------------メニュー開始-------------------- -->
<div id="menu">

<!-- 日付検索のタブ -->
<label class="calendar_tab" for="calendar_search"><span>日付検索</span></label>
<input type="checkbox" id="calendar_search" class="on-off" />
<form class="calendar_form">
	<div id="calendar"></div>
	<script src="make_calendar.js"></script>
</form>
<!-- お気に入り検索のタブ -->
<label class="favorite_tab" for="favorite_search"><span>お気に入り検索</span></label>
<input type="checkbox" id="favorite_search" class="on-off" />
<form class="favorite_form" method="GET" action="search_result_page.php">
	<dl>
		<dt>星の数:
			<dd><label><input type="checkbox" name="star_1" value="enable" checked>★1</label>
			    <label><input type="checkbox" name="star_2" value="enable" checked>★2</label>
			    <label><input type="checkbox" name="star_3" value="enable" checked>★3</label>
			    <label><input type="checkbox" name="star_4" value="enable" checked>★4</label>
			    <label><input type="checkbox" name="star_5" value="enable" checked>★5</label>
			</dd>
		</dt>
		<dt>表示順:
			<dd><select name="stars_sort">
			    <option value="no_sort">-</option>
			    <option value="Many_first">星が多い順</option>
			    <option value="Small_first">星が少ない順</option>
			    </select>
			</dd>
			<dd><input type="submit" value="検索">
			    <input type="reset" value="リセット">
			    <input type="hidden" name="via_page" value="favorite">
			</dd>
		</dt>
	</dl>
</form>

<!-- 詳細検索のタブ -->
<label class="detailed_tab" for="detailed_search"><span>詳細検索</span></label>
<input type="checkbox" id="detailed_search" class="on-off" />
<form class="detailed_form" name="detailed_form" method="GET" action="search_result_page.php">
	<dl>
		<dt>期間:<br>
			<dd class="date_dd"><select name="from_century" onChange="last_day_func('from')">
			    <option value="19" >19</option>
			    <option value="20" selected>20</option>
			    </select>

			    <select name="from_year" onChange="last_day_func('from')">
			    <option value="00">00</option>
			    <option value="01">01</option>
			    <option value="02">02</option>
			    <option value="03">03</option>
			    <option value="04">04</option>
			    <option value="05">05</option>
			    <option value="06">06</option>
			    <option value="07">07</option>
			    <option value="08">08</option>
			    <option value="09">09</option>
			    <option value="10">10</option>
			    <option value="11">11</option>
			    <option value="12">12</option>
			    <option value="13">13</option>
			    <option value="14">14</option>
			    <option value="15">15</option>
			    <option value="16">16</option>
			    <option value="17">17</option>
			    <option value="18">18</option>
			    <option value="19">19</option>
			    <option value="20">20</option>
			    <option value="21">21</option>
			    <option value="22">22</option>
			    <option value="23">23</option>
			    <option value="24">24</option>
			    <option value="25">25</option>
			    <option value="26">26</option>
			    <option value="27">27</option>
			    <option value="28">28</option>
			    <option value="29">29</option>
			    <option value="30">30</option>
			    <option value="31">31</option>
			    <option value="32">32</option>
			    <option value="33">33</option>
			    <option value="34">34</option>
			    <option value="35">35</option>
			    <option value="36">36</option>
			    <option value="37">37</option>
			    <option value="38">38</option>
			    <option value="39">39</option>
			    <option value="40">40</option>
			    <option value="41">41</option>
			    <option value="42">42</option>
			    <option value="43">43</option>
			    <option value="44">44</option>
			    <option value="45">45</option>
			    <option value="46">46</option>
			    <option value="47">47</option>
			    <option value="48">48</option>
			    <option value="49">49</option>
			    <option value="50">50</option>
			    <option value="51">51</option>
			    <option value="52">52</option>
			    <option value="53">53</option>
			    <option value="54">54</option>
			    <option value="55">55</option>
			    <option value="56">56</option>
			    <option value="57">57</option>
			    <option value="58">58</option>
			    <option value="59">59</option>
			    <option value="60">60</option>
			    <option value="61">61</option>
			    <option value="62">62</option>
			    <option value="63">63</option>
			    <option value="64">64</option>
			    <option value="65">65</option>
			    <option value="66">66</option>
			    <option value="67">67</option>
			    <option value="68">68</option>
			    <option value="69">69</option>
			    <option value="70">70</option>
			    <option value="71">71</option>
			    <option value="72">72</option>
			    <option value="73">73</option>
			    <option value="74">74</option>
			    <option value="75">75</option>
			    <option value="76">76</option>
			    <option value="77">77</option>
			    <option value="78">78</option>
			    <option value="79">79</option>
			    <option value="80">80</option>
			    <option value="81">81</option>
			    <option value="82">82</option>
			    <option value="83">83</option>
			    <option value="84">84</option>
			    <option value="85">85</option>
			    <option value="86">86</option>
			    <option value="87">87</option>
			    <option value="88">88</option>
			    <option value="89">89</option>
			    <option value="90">90</option>
			    <option value="91">91</option>
			    <option value="92">92</option>
			    <option value="93">93</option>
			    <option value="94">94</option>
			    <option value="95">95</option>
			    <option value="96">96</option>
			    <option value="97">97</option>
			    <option value="98">98</option>
			    <option value="99">99</option>
			    </select>年

			    <select name="from_month" onChange="last_day_func('from')">
			    <option value="01">1</option>
			    <option value="02">2</option>
			    <option value="03">3</option>
			    <option value="04">4</option>
			    <option value="05">5</option>
			    <option value="06">6</option>
			    <option value="07">7</option>
			    <option value="08">8</option>
			    <option value="09">9</option>
			    <option value="10">10</option>
			    <option value="11">11</option>
			    <option value="12">12</option>
			    </select>月

			    <select name="from_day">
			    <option value="01">1</option>
			    <option value="02">2</option>
			    <option value="03">3</option>
			    <option value="04">4</option>
			    <option value="05">5</option>
			    <option value="06">6</option>
			    <option value="07">7</option>
			    <option value="08">8</option>
			    <option value="09">9</option>
			    <option value="10">10</option>
			    <option value="11">11</option>
			    <option value="12">12</option>
			    <option value="13">13</option>
			    <option value="14">14</option>
			    <option value="15">15</option>
			    <option value="16">16</option>
			    <option value="17">17</option>
			    <option value="18">18</option>
			    <option value="19">19</option>
			    <option value="20">20</option>
			    <option value="21">21</option>
			    <option value="22">22</option>
			    <option value="23">23</option>
			    <option value="24">24</option>
			    <option value="25">25</option>
			    <option value="26">26</option>
			    <option value="27">27</option>
			    <option value="28">28</option>
			    <option value="29">29</option>
			    <option value="30">30</option>
			    <option value="31">31</option>
			    </select>日<br>
			    ～<br>
			    <select name="till_century" onChange="last_day_func('till')">
			    <option value="19"<?php echo $now_century[1] ;?>>19</option>
			    <option value="20"<?php echo $now_century[2] ;?>>20</option>
			    </select>

			    <select name="till_year" onChange="last_day_func('till')">
			    <option value="00"<?php echo $now_year[0] ;?>>00</option>
			    <option value="01"<?php echo $now_year[1] ;?>>01</option>
			    <option value="02"<?php echo $now_year[2] ;?>>02</option>
			    <option value="03"<?php echo $now_year[3] ;?>>03</option>
			    <option value="04"<?php echo $now_year[4] ;?>>04</option>
			    <option value="05"<?php echo $now_year[5] ;?>>05</option>
			    <option value="06"<?php echo $now_year[6] ;?>>06</option>
			    <option value="07"<?php echo $now_year[7] ;?>>07</option>
			    <option value="08"<?php echo $now_year[8] ;?>>08</option>
			    <option value="09"<?php echo $now_year[9] ;?>>09</option>
			    <option value="10"<?php echo $now_year[10] ;?>>10</option>
			    <option value="11"<?php echo $now_year[11] ;?>>11</option>
			    <option value="12"<?php echo $now_year[12] ;?>>12</option>
			    <option value="13"<?php echo $now_year[13] ;?>>13</option>
			    <option value="14"<?php echo $now_year[14] ;?>>14</option>
			    <option value="15"<?php echo $now_year[15] ;?>>15</option>
			    <option value="16"<?php echo $now_year[16] ;?>>16</option>
			    <option value="17"<?php echo $now_year[17] ;?>>17</option>
			    <option value="18"<?php echo $now_year[18] ;?>>18</option>
			    <option value="19"<?php echo $now_year[19] ;?>>19</option>
			    <option value="20"<?php echo $now_year[20] ;?>>20</option>
			    <option value="21"<?php echo $now_year[21] ;?>>21</option>
			    <option value="22"<?php echo $now_year[22] ;?>>22</option>
			    <option value="23"<?php echo $now_year[23] ;?>>23</option>
			    <option value="24"<?php echo $now_year[24] ;?>>24</option>
			    <option value="25"<?php echo $now_year[25] ;?>>25</option>
			    <option value="26"<?php echo $now_year[26] ;?>>26</option>
			    <option value="27"<?php echo $now_year[27] ;?>>27</option>
			    <option value="28"<?php echo $now_year[28] ;?>>28</option>
			    <option value="29"<?php echo $now_year[29] ;?>>29</option>
			    <option value="30"<?php echo $now_year[30] ;?>>30</option>
			    <option value="31"<?php echo $now_year[31] ;?>>31</option>
			    <option value="32"<?php echo $now_year[32] ;?>>32</option>
			    <option value="33"<?php echo $now_year[33] ;?>>33</option>
			    <option value="34"<?php echo $now_year[34] ;?>>34</option>
			    <option value="35"<?php echo $now_year[35] ;?>>35</option>
			    <option value="36"<?php echo $now_year[36] ;?>>36</option>
			    <option value="37"<?php echo $now_year[37] ;?>>37</option>
			    <option value="38"<?php echo $now_year[38] ;?>>38</option>
			    <option value="39"<?php echo $now_year[39] ;?>>39</option>
			    <option value="40"<?php echo $now_year[40] ;?>>40</option>
			    <option value="41"<?php echo $now_year[41] ;?>>41</option>
			    <option value="42"<?php echo $now_year[42] ;?>>42</option>
			    <option value="43"<?php echo $now_year[43] ;?>>43</option>
			    <option value="44"<?php echo $now_year[44] ;?>>44</option>
			    <option value="45"<?php echo $now_year[45] ;?>>45</option>
			    <option value="46"<?php echo $now_year[46] ;?>>46</option>
			    <option value="47"<?php echo $now_year[47] ;?>>47</option>
			    <option value="48"<?php echo $now_year[48] ;?>>48</option>
			    <option value="49"<?php echo $now_year[49] ;?>>49</option>
			    <option value="50"<?php echo $now_year[50] ;?>>50</option>
			    <option value="51"<?php echo $now_year[51] ;?>>51</option>
			    <option value="52"<?php echo $now_year[52] ;?>>52</option>
			    <option value="53"<?php echo $now_year[53] ;?>>53</option>
			    <option value="54"<?php echo $now_year[54] ;?>>54</option>
			    <option value="55"<?php echo $now_year[55] ;?>>55</option>
			    <option value="56"<?php echo $now_year[56] ;?>>56</option>
			    <option value="57"<?php echo $now_year[57] ;?>>57</option>
			    <option value="58"<?php echo $now_year[58] ;?>>58</option>
			    <option value="59"<?php echo $now_year[59] ;?>>59</option>
			    <option value="60"<?php echo $now_year[60] ;?>>60</option>
			    <option value="61"<?php echo $now_year[61] ;?>>61</option>
			    <option value="62"<?php echo $now_year[62] ;?>>62</option>
			    <option value="63"<?php echo $now_year[63] ;?>>63</option>
			    <option value="64"<?php echo $now_year[64] ;?>>64</option>
			    <option value="65"<?php echo $now_year[65] ;?>>65</option>
			    <option value="66"<?php echo $now_year[66] ;?>>66</option>
			    <option value="67"<?php echo $now_year[67] ;?>>67</option>
			    <option value="68"<?php echo $now_year[68] ;?>>68</option>
			    <option value="69"<?php echo $now_year[69] ;?>>69</option>
			    <option value="70"<?php echo $now_year[70] ;?>>70</option>
			    <option value="71"<?php echo $now_year[71] ;?>>71</option>
			    <option value="72"<?php echo $now_year[72] ;?>>72</option>
			    <option value="73"<?php echo $now_year[73] ;?>>73</option>
			    <option value="74"<?php echo $now_year[74] ;?>>74</option>
			    <option value="75"<?php echo $now_year[75] ;?>>75</option>
			    <option value="76"<?php echo $now_year[76] ;?>>76</option>
			    <option value="77"<?php echo $now_year[77] ;?>>77</option>
			    <option value="78"<?php echo $now_year[78] ;?>>78</option>
			    <option value="79"<?php echo $now_year[79] ;?>>79</option>
			    <option value="80"<?php echo $now_year[80] ;?>>80</option>
			    <option value="81"<?php echo $now_year[81] ;?>>81</option>
			    <option value="82"<?php echo $now_year[82] ;?>>82</option>
			    <option value="83"<?php echo $now_year[83] ;?>>83</option>
			    <option value="84"<?php echo $now_year[84] ;?>>84</option>
			    <option value="85"<?php echo $now_year[85] ;?>>85</option>
			    <option value="86"<?php echo $now_year[86] ;?>>86</option>
			    <option value="87"<?php echo $now_year[87] ;?>>87</option>
			    <option value="88"<?php echo $now_year[88] ;?>>88</option>
			    <option value="89"<?php echo $now_year[89] ;?>>89</option>
			    <option value="90"<?php echo $now_year[90] ;?>>90</option>
			    <option value="91"<?php echo $now_year[91] ;?>>91</option>
			    <option value="92"<?php echo $now_year[92] ;?>>92</option>
			    <option value="93"<?php echo $now_year[93] ;?>>93</option>
			    <option value="94"<?php echo $now_year[94] ;?>>94</option>
			    <option value="95"<?php echo $now_year[95] ;?>>95</option>
			    <option value="96"<?php echo $now_year[96] ;?>>96</option>
			    <option value="97"<?php echo $now_year[97] ;?>>97</option>
			    <option value="98"<?php echo $now_year[98] ;?>>98</option>
			    <option value="99"<?php echo $now_year[99] ;?>>99</option>
			    </select>年

			    <select name="till_month" onChange="last_day_func('till')">
			    <option value="01"<?php echo $now_month[1] ;?>>1</option>
			    <option value="02"<?php echo $now_month[2] ;?>>2</option>
			    <option value="03"<?php echo $now_month[3] ;?>>3</option>
			    <option value="04"<?php echo $now_month[4] ;?>>4</option>
			    <option value="05"<?php echo $now_month[5] ;?>>5</option>
			    <option value="06"<?php echo $now_month[6] ;?>>6</option>
			    <option value="07"<?php echo $now_month[7] ;?>>7</option>
			    <option value="08"<?php echo $now_month[8] ;?>>8</option>
			    <option value="09"<?php echo $now_month[9] ;?>>9</option>
			    <option value="10"<?php echo $now_month[10] ;?>>10</option>
			    <option value="11"<?php echo $now_month[11] ;?>>11</option>
			    <option value="12"<?php echo $now_month[12] ;?>>12</option>
			    </select>月

			    <select name="till_day">
			    <option value="01"<?php echo $now_day[1] ;?>>1</option>
			    <option value="02"<?php echo $now_day[2] ;?>>2</option>
			    <option value="03"<?php echo $now_day[3] ;?>>3</option>
			    <option value="04"<?php echo $now_day[4] ;?>>4</option>
			    <option value="05"<?php echo $now_day[5] ;?>>5</option>
			    <option value="06"<?php echo $now_day[6] ;?>>6</option>
			    <option value="07"<?php echo $now_day[7] ;?>>7</option>
			    <option value="08"<?php echo $now_day[8] ;?>>8</option>
			    <option value="09"<?php echo $now_day[9] ;?>>9</option>
			    <option value="10"<?php echo $now_day[10] ;?>>10</option>
			    <option value="11"<?php echo $now_day[11] ;?>>11</option>
			    <option value="12"<?php echo $now_day[12] ;?>>12</option>
			    <option value="13"<?php echo $now_day[13] ;?>>13</option>
			    <option value="14"<?php echo $now_day[14] ;?>>14</option>
			    <option value="15"<?php echo $now_day[15] ;?>>15</option>
			    <option value="16"<?php echo $now_day[16] ;?>>16</option>
			    <option value="17"<?php echo $now_day[17] ;?>>17</option>
			    <option value="18"<?php echo $now_day[18] ;?>>18</option>
			    <option value="19"<?php echo $now_day[19] ;?>>19</option>
			    <option value="20"<?php echo $now_day[20] ;?>>20</option>
			    <option value="21"<?php echo $now_day[21] ;?>>21</option>
			    <option value="22"<?php echo $now_day[22] ;?>>22</option>
			    <option value="23"<?php echo $now_day[23] ;?>>23</option>
			    <option value="24"<?php echo $now_day[24] ;?>>24</option>
			    <option value="25"<?php echo $now_day[25] ;?>>25</option>
			    <option value="26"<?php echo $now_day[26] ;?>>26</option>
			    <option value="27"<?php echo $now_day[27] ;?>>27</option>
			    <option value="28"<?php echo $now_day[28] ;?>>28</option>
			    <option value="29"<?php echo $now_day[29] ;?>>29</option>
			    <option value="30"<?php echo $now_day[30] ;?>>30</option>
			    <option value="31"<?php echo $now_day[31] ;?>>31</option>
			    </select>日
			</dd>
		</dt>
		<dt>日付のソート:
			<dd><select name="days_sort">
			    <option value="new_first">新しい順</option>
			    <option value="old_first">古い順</option>
			    </select>
			</dd>
		</dt>
		<dt>星の数:
			<dd><label><input type="checkbox" name="star_1" value="enable" checked>★1</label>
			    <label><input type="checkbox" name="star_2" value="enable" checked>★2</label>
			    <label><input type="checkbox" name="star_3" value="enable" checked>★3</label>
			    <label><input type="checkbox" name="star_4" value="enable" checked>★4</label>
			    <label><input type="checkbox" name="star_5" value="enable" checked>★5</label>
			</dd>
		</dt>
		<dt>星のソート:
			<dd><select name="stars_sort">
			    <option value="no_sort">-</option>
			    <option value="Many_first">多い順</option>
			    <option value="Small_first">少ない順</option>
			    </select>
			</dd>
		</dt>
		<dt>ソート順:
			<dd><select name="sort_sort">
			    <option value="days_first">日付順</option>
			    <option value="starts_first">お気に入り順</option>
			    </select>
			</dd>
		</dt>
		<dt>撮影カメラ:
			<dd><select class="camera_code" name="camera_code">
			    <?php echo $make_camera_name_option; ?>
			    </select>
			</dd>
		</dt>
		<dt>キーワード:
			<dd><input type="text" name="key_word">
			</dd>
		</dt>
		<dt>
			<dd><input type="submit" value="検索">
			    <input type="reset" value="リセット">
			    <input type="hidden" name="via_page" value="detailed">
			</dd>
		</dt>
	</dl>

</form>

<a class="upload_page_link" href="upload_page.php"><span>写真のアップロード</span></a>
<a class="user_config_link" href="user_config_page.php"><span>ユーザ設定変更</span></a>

</div>
<!-- --------------------メニュー終了-------------------- -->

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