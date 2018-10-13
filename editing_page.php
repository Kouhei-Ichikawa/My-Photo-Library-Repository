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
//無効な日付を回避するスクリプト
//ページを開いたときに実行する記述
window.onload = function () {
	last_day_func('edit');
};

//カメラ名の入力フォームの表示を変更するスクリプト
function form_select_func(){

obj = document.editing_form.form_select;
index = obj.selectedIndex;

if (obj.options[index].value == "existing_camera"){
	//「既存のカメラ名から選択」が選ばれていたらプルダウンメニューを表示
	document.editing_form.camera_name_select.className = "camera_name_select";
	document.editing_form.camera_name_input.className = "passive";
}else if(obj.options[index].value == "new_camera"){
	//「新しいカメラ名を入力」が選ばれていたらテキストボックスを表示
	document.editing_form.camera_name_select.className = "passive";
	document.editing_form.camera_name_input.className = "camera_name_input";
}
//終わり
}
</script>

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

//画像表示ページから渡されたデータを取得
$show_row=$_GET['show_row'];

//まず情報を編集する対象の現在の値を取得する
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
	$title = "";
}
$favorites_level = $row[4];
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

//撮影日のプルダウンメニューの初期値を指定する為、$filming_dateを元に変数を作成
$array_filming_date = array();
$array_filming_date = explode("/",$filming_date);

//以下から世紀の箇所のデフォルト値を設定する処理
$tmp_century = substr($array_filming_date[0] ,0 ,2 ); 
IF($tmp_century == 19){
	//19世紀のプルダウンメニューがデフォルトになる
	$filming_century = array(""," selected","");
}else{
	//20世紀のプルダウンメニューがデフォルトになる
	$filming_century = array("",""," selected");
}

//以下から年代の箇所のデフォルト値を設定する処理
$tmp_year = substr($array_filming_date[0] ,2 ,2 );
$filming_year = array();
//$filming_yearに空白の要素を100個作る(0~99まで)
for($i=0 ; $i<100 ; $i++){
	$filming_year[] = "";
}
//年代の数字と同じ番号の配列がデフォルトになる
$filming_year[$tmp_year] = " selected";

//以下から月の箇所のデフォルト値を設定する処理
//$filming_dateから作成した配列の値が文字列と認識されることがあるのでintvalで数値型に変換
$tmp_month = intval($array_filming_date[1]);
$filming_month = array("");
//$filming_monthに空白の要素を12個作る
for($i=1 ; $i<13 ; $i++){
	$filming_month[] = "";
}
//月の数字と同じ番号の配列がデフォルトになる
$filming_month[$tmp_month] = " selected";

//以下から日の箇所のデフォルト値を設定する処理
//$filming_dateから作成した配列の値が文字列と認識されることがあるのでintvalで数値型に変換
$tmp_day = intval($array_filming_date[2]);
$filming_day = array("");
//$filming_dayに空白の要素を31個作る
for($i=1 ; $i<32 ; $i++){
	$filming_day[] = "";
}
//日の数字と同じ番号の配列がデフォルトになる
$filming_day[$tmp_day] = " selected";

//以下からお気に入り度の箇所のデフォルト値を設定する処理
$star_default = array("");
//$star_defaultに空白の要素を5個作る
for($i=1 ; $i<6 ; $i++){
	$star_default[] = "";
}
$star_default[$favorites_level] = " checked";

//以下からカメラ名のプルダウンの作成とデフォルト値を設定する処理
//プルダウンメニューの記述を格納する変数を宣言
//中身の例 → <option value="カメラコード">カメラ名</option>
$make_camera_name_option = "<option value=\"\">Unknown</option>\n";

//変更前のカメラコードのデフォルト値を設定
$before_camera_code = "";

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
			$make_camera_name_option = $make_camera_name_option . "\t\t\t\t\t<option value=\"" . $item . "\"";
		}else{
			//カメラ名を変数に格納
			IF($camera_name == $item){
				//撮影カメラと同じだった場合はプルダウンメニューのデフォルトに設定
				$make_camera_name_option = $make_camera_name_option . " selected>" . $item . "</option>\n";
				//変更前のカメラコードを記録
				$before_camera_code = $row[0];
			}else{
				$make_camera_name_option = $make_camera_name_option . ">" . $item . "</option>\n";
			}
		}
	}
}

//以下から非表示フラグの箇所のデフォルト値を設定する処理
IF($secret_flug == 0){
	//フラグが0の場合はoff
	$secret_flug_default = array(" selected","");
}else{
	//フラグが1の場合はon
	$secret_flug_default = array(""," selected");
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

<!-- ------------------ 編集ページ開始 ------------------ -->
<div id="editing_page">
	<p>登録情報編集</p>
	<div class="photo_view"><img src=<?php print $file_pass; ?>></div>
	<form class="editing_form" method="post" name="editing_form">
		<dl class="data_edit">
			<dt>タイトル:
				<dd><input type="text" class="title_input" name="title_input" maxlength='50' placeholder="No title" value="<?php print $title; ?>"></dd>
			</dt>
			<dt>撮影日:
				<dd>
					<select class="filming_century_select" name="filming_century_select" onChange="last_day_func('edit')">
					<option value="19"<?php echo $filming_century[1]; ?>>19</option>
					<option value="20"<?php echo $filming_century[2]; ?>>20</option>
					</select>

					<select class="filming_year_select" name="filming_year_select" onChange="last_day_func('edit')">
					<option value="00"<?php echo $filming_year[0]; ?>>00</option>
					<option value="01"<?php echo $filming_year[1]; ?>>01</option>
					<option value="02"<?php echo $filming_year[2]; ?>>02</option>
					<option value="03"<?php echo $filming_year[3]; ?>>03</option>
					<option value="04"<?php echo $filming_year[4]; ?>>04</option>
					<option value="05"<?php echo $filming_year[5]; ?>>05</option>
					<option value="06"<?php echo $filming_year[6]; ?>>06</option>
					<option value="07"<?php echo $filming_year[7]; ?>>07</option>
					<option value="08"<?php echo $filming_year[8]; ?>>08</option>
					<option value="09"<?php echo $filming_year[9]; ?>>09</option>
					<option value="10"<?php echo $filming_year[10]; ?>>10</option>
					<option value="11"<?php echo $filming_year[11]; ?>>11</option>
					<option value="12"<?php echo $filming_year[12]; ?>>12</option>
					<option value="13"<?php echo $filming_year[13]; ?>>13</option>
					<option value="14"<?php echo $filming_year[14]; ?>>14</option>
					<option value="15"<?php echo $filming_year[15]; ?>>15</option>
					<option value="16"<?php echo $filming_year[16]; ?>>16</option>
					<option value="17"<?php echo $filming_year[17]; ?>>17</option>
					<option value="18"<?php echo $filming_year[18]; ?>>18</option>
					<option value="19"<?php echo $filming_year[19]; ?>>19</option>
					<option value="20"<?php echo $filming_year[20]; ?>>20</option>
					<option value="21"<?php echo $filming_year[21]; ?>>21</option>
					<option value="22"<?php echo $filming_year[22]; ?>>22</option>
					<option value="23"<?php echo $filming_year[23]; ?>>23</option>
					<option value="24"<?php echo $filming_year[24]; ?>>24</option>
					<option value="25"<?php echo $filming_year[25]; ?>>25</option>
					<option value="26"<?php echo $filming_year[26]; ?>>26</option>
					<option value="27"<?php echo $filming_year[27]; ?>>27</option>
					<option value="28"<?php echo $filming_year[28]; ?>>28</option>
					<option value="29"<?php echo $filming_year[29]; ?>>29</option>
					<option value="30"<?php echo $filming_year[30]; ?>>30</option>
					<option value="31"<?php echo $filming_year[31]; ?>>31</option>
					<option value="32"<?php echo $filming_year[32]; ?>>32</option>
					<option value="33"<?php echo $filming_year[33]; ?>>33</option>
					<option value="34"<?php echo $filming_year[34]; ?>>34</option>
					<option value="35"<?php echo $filming_year[35]; ?>>35</option>
					<option value="36"<?php echo $filming_year[36]; ?>>36</option>
					<option value="37"<?php echo $filming_year[37]; ?>>37</option>
					<option value="38"<?php echo $filming_year[38]; ?>>38</option>
					<option value="39"<?php echo $filming_year[39]; ?>>39</option>
					<option value="40"<?php echo $filming_year[40]; ?>>40</option>
					<option value="41"<?php echo $filming_year[41]; ?>>41</option>
					<option value="42"<?php echo $filming_year[42]; ?>>42</option>
					<option value="43"<?php echo $filming_year[43]; ?>>43</option>
					<option value="44"<?php echo $filming_year[44]; ?>>44</option>
					<option value="45"<?php echo $filming_year[45]; ?>>45</option>
					<option value="46"<?php echo $filming_year[46]; ?>>46</option>
					<option value="47"<?php echo $filming_year[47]; ?>>47</option>
					<option value="48"<?php echo $filming_year[48]; ?>>48</option>
					<option value="49"<?php echo $filming_year[49]; ?>>49</option>
					<option value="50"<?php echo $filming_year[50]; ?>>50</option>
					<option value="51"<?php echo $filming_year[51]; ?>>51</option>
					<option value="52"<?php echo $filming_year[52]; ?>>52</option>
					<option value="53"<?php echo $filming_year[53]; ?>>53</option>
					<option value="54"<?php echo $filming_year[54]; ?>>54</option>
					<option value="55"<?php echo $filming_year[55]; ?>>55</option>
					<option value="56"<?php echo $filming_year[56]; ?>>56</option>
					<option value="57"<?php echo $filming_year[57]; ?>>57</option>
					<option value="58"<?php echo $filming_year[58]; ?>>58</option>
					<option value="59"<?php echo $filming_year[59]; ?>>59</option>
					<option value="60"<?php echo $filming_year[60]; ?>>60</option>
					<option value="61"<?php echo $filming_year[61]; ?>>61</option>
					<option value="62"<?php echo $filming_year[62]; ?>>62</option>
					<option value="63"<?php echo $filming_year[63]; ?>>63</option>
					<option value="64"<?php echo $filming_year[64]; ?>>64</option>
					<option value="65"<?php echo $filming_year[65]; ?>>65</option>
					<option value="66"<?php echo $filming_year[66]; ?>>66</option>
					<option value="67"<?php echo $filming_year[67]; ?>>67</option>
					<option value="68"<?php echo $filming_year[68]; ?>>68</option>
					<option value="69"<?php echo $filming_year[69]; ?>>69</option>
					<option value="70"<?php echo $filming_year[70]; ?>>70</option>
					<option value="71"<?php echo $filming_year[71]; ?>>71</option>
					<option value="72"<?php echo $filming_year[72]; ?>>72</option>
					<option value="73"<?php echo $filming_year[73]; ?>>73</option>
					<option value="74"<?php echo $filming_year[74]; ?>>74</option>
					<option value="75"<?php echo $filming_year[75]; ?>>75</option>
					<option value="76"<?php echo $filming_year[76]; ?>>76</option>
					<option value="77"<?php echo $filming_year[77]; ?>>77</option>
					<option value="78"<?php echo $filming_year[78]; ?>>78</option>
					<option value="79"<?php echo $filming_year[79]; ?>>79</option>
					<option value="80"<?php echo $filming_year[80]; ?>>80</option>
					<option value="81"<?php echo $filming_year[81]; ?>>81</option>
					<option value="82"<?php echo $filming_year[82]; ?>>82</option>
					<option value="83"<?php echo $filming_year[83]; ?>>83</option>
					<option value="84"<?php echo $filming_year[84]; ?>>84</option>
					<option value="85"<?php echo $filming_year[85]; ?>>85</option>
					<option value="86"<?php echo $filming_year[86]; ?>>86</option>
					<option value="87"<?php echo $filming_year[87]; ?>>87</option>
					<option value="88"<?php echo $filming_year[88]; ?>>88</option>
					<option value="89"<?php echo $filming_year[89]; ?>>89</option>
					<option value="90"<?php echo $filming_year[90]; ?>>90</option>
					<option value="91"<?php echo $filming_year[91]; ?>>91</option>
					<option value="92"<?php echo $filming_year[92]; ?>>92</option>
					<option value="93"<?php echo $filming_year[93]; ?>>93</option>
					<option value="94"<?php echo $filming_year[94]; ?>>94</option>
					<option value="95"<?php echo $filming_year[95]; ?>>95</option>
					<option value="96"<?php echo $filming_year[96]; ?>>96</option>
					<option value="97"<?php echo $filming_year[97]; ?>>97</option>
					<option value="98"<?php echo $filming_year[98]; ?>>98</option>
					<option value="99"<?php echo $filming_year[99]; ?>>99</option>
					</select>年

					<select class="filming_month_select" name="filming_month_select" onChange="last_day_func('edit')">
					<option value="01"<?php echo $filming_month[1]; ?>>1</option>
					<option value="02"<?php echo $filming_month[2]; ?>>2</option>
					<option value="03"<?php echo $filming_month[3]; ?>>3</option>
					<option value="04"<?php echo $filming_month[4]; ?>>4</option>
					<option value="05"<?php echo $filming_month[5]; ?>>5</option>
					<option value="06"<?php echo $filming_month[6]; ?>>6</option>
					<option value="07"<?php echo $filming_month[7]; ?>>7</option>
					<option value="08"<?php echo $filming_month[8]; ?>>8</option>
					<option value="09"<?php echo $filming_month[9]; ?>>9</option>
					<option value="10"<?php echo $filming_month[10]; ?>>10</option>
					<option value="11"<?php echo $filming_month[11]; ?>>11</option>
					<option value="12"<?php echo $filming_month[12]; ?>>12</option>
					</select>月

					<select class="filming_day_select" name="filming_day_select">
					<option value="01"<?php echo $filming_day[1]; ?>>1</option>
					<option value="02"<?php echo $filming_day[2]; ?>>2</option>
					<option value="03"<?php echo $filming_day[3]; ?>>3</option>
					<option value="04"<?php echo $filming_day[4]; ?>>4</option>
					<option value="05"<?php echo $filming_day[5]; ?>>5</option>
					<option value="06"<?php echo $filming_day[6]; ?>>6</option>
					<option value="07"<?php echo $filming_day[7]; ?>>7</option>
					<option value="08"<?php echo $filming_day[8]; ?>>8</option>
					<option value="09"<?php echo $filming_day[9]; ?>>9</option>
					<option value="10"<?php echo $filming_day[10]; ?>>10</option>
					<option value="11"<?php echo $filming_day[11]; ?>>11</option>
					<option value="12"<?php echo $filming_day[12]; ?>>12</option>
					<option value="13"<?php echo $filming_day[13]; ?>>13</option>
					<option value="14"<?php echo $filming_day[14]; ?>>14</option>
					<option value="15"<?php echo $filming_day[15]; ?>>15</option>
					<option value="16"<?php echo $filming_day[16]; ?>>16</option>
					<option value="17"<?php echo $filming_day[17]; ?>>17</option>
					<option value="18"<?php echo $filming_day[18]; ?>>18</option>
					<option value="19"<?php echo $filming_day[19]; ?>>19</option>
					<option value="20"<?php echo $filming_day[20]; ?>>20</option>
					<option value="21"<?php echo $filming_day[21]; ?>>21</option>
					<option value="22"<?php echo $filming_day[22]; ?>>22</option>
					<option value="23"<?php echo $filming_day[23]; ?>>23</option>
					<option value="24"<?php echo $filming_day[24]; ?>>24</option>
					<option value="25"<?php echo $filming_day[25]; ?>>25</option>
					<option value="26"<?php echo $filming_day[26]; ?>>26</option>
					<option value="27"<?php echo $filming_day[27]; ?>>27</option>
					<option value="28"<?php echo $filming_day[28]; ?>>28</option>
					<option value="29"<?php echo $filming_day[29]; ?>>29</option>
					<option value="30"<?php echo $filming_day[30]; ?>>30</option>
					<option value="31"<?php echo $filming_day[31]; ?>>31</option>
					</select>日

				</dd>
			</dt>
			<dt>番号:
				<dd><input type="tel" class="film_number_input" name="film_number_input" maxlength='4' value="<?php print $film_number; ?>"></dd>
			</dt>
			<dt>お気に入り:
				<dd>
					<label><input type="radio" class="stars_select" name="stars_select" value="1"<?php print $star_default[1]; ?>>★1</label>
					<label><input type="radio" class="stars_select" name="stars_select" value="2"<?php print $star_default[2]; ?>>★2</label>
					<label><input type="radio" class="stars_select" name="stars_select" value="3"<?php print $star_default[3]; ?>>★3</label>
					<label><input type="radio" class="stars_select" name="stars_select" value="4"<?php print $star_default[4]; ?>>★4</label>
					<label><input type="radio" class="stars_select" name="stars_select" value="5"<?php print $star_default[5]; ?>>★5</label>
				</dd>
			</dt>
			<dt>撮影カメラ:
				<dd class="camera_select_dd">
					<select class="form_select" name="form_select" onChange="form_select_func()">
					<option value="existing_camera">既存のカメラ名から選択</option>
					<option value="new_camera">新しいカメラ名を入力</option>
					</select>

					<!-- 既存のカメラ名から選択 -->
					<select class="camera_name_select" name="camera_name_select">
					<?php echo $make_camera_name_option; ?>
					</select>

					<!-- 新しいカメラ名を入力 -->
					<input class="passive" type="text" name="camera_name_input" maxlength='30' placeholder="カメラ名">
				</dd>
			</dt>
			<dt>非表示フラグ:
				<dd>
					<select class="secret_flug_select" name="secret_flug_select">
					<option value="0"<?php echo $secret_flug_default[0]; ?>>OFF</option>
					<option value="1"<?php echo $secret_flug_default[1]; ?>>ON</option>
					</select>
				</dd>
			</dt>
			<dt>メモ:
				<dd><input type="text" class="memo_input" name="memo_input"  maxlength='140' value="<?php print $memo; ?>"></dd>
			</dt>
			<dt>
				<dd>
				<button class="editing_page_submit" name="editing_page_submit" type="button">保存</button>
				<input type="hidden" name="before_date" value="<?php echo $filming_date; ?>">
				<input type="hidden" name="before_number" value="<?php echo $film_number; ?>">
				<input type="hidden" name="before_camera_code" value="<?php echo $before_camera_code; ?>">
				<button type="button" onclick="history.back()">キャンセル</button>
				<br>
				<!-- フォームに入力した値が入力規則にあっていなかった場合はスクリプトでメッセージを表示 -->
				<span class="error_message"></span>
				</dd>
			</dt>
		</dl>
	</form>

	<!-- 入力規則の確認とupdata文を実行するスクリプト -->
	<script src="photo_info_editing.js"></script>

</div>
<!-- ------------------ 編集ページ終了 ------------------ -->

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