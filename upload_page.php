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
<script src="upload_page_func.js"></script>

<script>
//無効な日付を回避するスクリプト
//ページを開いたときに実行する記述
window.onload = function () {
	last_day_func('upload');
}

//スライドを作成するスクリプト
function thumbnail_slide(){
jQuery(function($) {
   $('#thumbnail_field').slick({
   slidesToShow:3,
   slidesToScroll:3,
   autoplay: false,
   autoplaySpeed:5000,
   pauseOnFocus: false,
   pauseOnHover: false,
   pauseOnDotsHover: false,
   swipe: false
 　});
});
}

//カメラ名の入力フォームの表示を変更するスクリプト
function form_select_func(camera_form_select){
	//呼び出し元の親要素を取得
	var dd_element = camera_form_select.parentNode;

	//カメラ名を選択するプルダウンとテキストボックスを変数に格納
	var camera_name_select = dd_element.getElementsByTagName('select').item(1);
	var camera_name_input = dd_element.getElementsByTagName('input').item(0);

	//フォーム選択のプルダウンで設定している値を取得
	var index = camera_form_select.selectedIndex;

	if (camera_form_select.options[index].value == "existing_camera"){
		//「既存のカメラ名から選択」が選ばれていたらプルダウンメニューを表示
		camera_name_select.className = "camera_name_select";
		camera_name_input.className = "passive";
	}else if(camera_form_select.options[index].value == "new_camera"){
		//「新しいカメラ名を入力」が選ばれていたらテキストボックスを表示
		camera_name_select.className = "passive";
		camera_name_input.className = "camera_name_input";
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

//以下から撮影日のプルダウンメニューの初期値の指定
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
//$now_yearに空白の要素を100個作る(0~99まで)
for($i=0 ; $i<100 ; $i++){
	$now_year[] = "";
}
//年代の数字と同じ番号の配列がデフォルトになる
$now_year[$tmp_year] = " selected";

//以下から月の箇所のデフォルト値を設定する処理
//$array_now_dateから作成した配列の値が文字列と認識されることがあるのでintvalで数値型に変換
$tmp_month = intval($array_now_date[1]);
$now_month = array("");
//$now_monthに空白の要素を12個作る
for($i=1 ; $i<13 ; $i++){
	$now_month[] = "";
}
//月の数字と同じ番号の配列がデフォルトになる
$now_month[$tmp_month] = " selected";

//以下から日の箇所のデフォルト値を設定する処理
//$array_now_dateから作成した配列の値が文字列と認識されることがあるのでintvalで数値型に変換
$tmp_day = intval($array_now_date[2]);
$now_day = array("");
//$now_dayに空白の要素を31個作る
for($i=1 ; $i<32 ; $i++){
	$now_day[] = "";
}
//日の数字と同じ番号の配列がデフォルトになる
$now_day[$tmp_day] = " selected";

//以下からカメラ名のプルダウンの作成とデフォルト値を設定する処理
//プルダウンメニューの記述を格納する変数を宣言
//中身の例 → <option value="カメラコード">カメラ名</option>
$make_camera_name_option = "<option value=\"\" selected>Unknown</option>\n";

//変更前のカメラコードのデフォルト値を設定
$before_camera_code = "";

//カメラ名を辞書順に取得するSQL文を作成
$camera_name_sql = "SELECT camera_code,camera_name FROM photo_operation.camera_table WHERE user_name = '" . $_SESSION['user_name'] . "' ORDER BY NLSSORT(camera_name,'NLS_SORT=Japanese')";

//データベースに接続
$conn = oci_connect("photo_retrieval","********","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

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
			$make_camera_name_option = $make_camera_name_option . ">" . $item . "</option>\n";
		}
	}
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

<!-- -------------- アップロードページ開始 -------------- -->
<div id="upload_page">
	<p class="page_name">アップロード</p>
	<!-- 撮影日を選択するフォームの箇所 -->
	<div id="step1_field">
	<p class="step_p">step.1 撮影日の選択</p>
	<form class="upload_date_form" name="upload_date_form" method="post">
		<select class="upload_century_select" name="upload_century_select" onChange="last_day_func('upload')">
		<option value="19"<?php echo $now_century[1]; ?>>19</option>
		<option value="20"<?php echo $now_century[2]; ?>>20</option>
		</select>

		<select class="upload_year_select" name="upload_year_select" onChange="last_day_func('upload')">
		<option value="00"<?php echo $now_year[0]; ?>>00</option>
		<option value="01"<?php echo $now_year[1]; ?>>01</option>
		<option value="02"<?php echo $now_year[2]; ?>>02</option>
		<option value="03"<?php echo $now_year[3]; ?>>03</option>
		<option value="04"<?php echo $now_year[4]; ?>>04</option>
		<option value="05"<?php echo $now_year[5]; ?>>05</option>
		<option value="06"<?php echo $now_year[6]; ?>>06</option>
		<option value="07"<?php echo $now_year[7]; ?>>07</option>
		<option value="08"<?php echo $now_year[8]; ?>>08</option>
		<option value="09"<?php echo $now_year[9]; ?>>09</option>
		<option value="10"<?php echo $now_year[10]; ?>>10</option>
		<option value="11"<?php echo $now_year[11]; ?>>11</option>
		<option value="12"<?php echo $now_year[12]; ?>>12</option>
		<option value="13"<?php echo $now_year[13]; ?>>13</option>
		<option value="14"<?php echo $now_year[14]; ?>>14</option>
		<option value="15"<?php echo $now_year[15]; ?>>15</option>
		<option value="16"<?php echo $now_year[16]; ?>>16</option>
		<option value="17"<?php echo $now_year[17]; ?>>17</option>
		<option value="18"<?php echo $now_year[18]; ?>>18</option>
		<option value="19"<?php echo $now_year[19]; ?>>19</option>
		<option value="20"<?php echo $now_year[20]; ?>>20</option>
		<option value="21"<?php echo $now_year[21]; ?>>21</option>
		<option value="22"<?php echo $now_year[22]; ?>>22</option>
		<option value="23"<?php echo $now_year[23]; ?>>23</option>
		<option value="24"<?php echo $now_year[24]; ?>>24</option>
		<option value="25"<?php echo $now_year[25]; ?>>25</option>
		<option value="26"<?php echo $now_year[26]; ?>>26</option>
		<option value="27"<?php echo $now_year[27]; ?>>27</option>
		<option value="28"<?php echo $now_year[28]; ?>>28</option>
		<option value="29"<?php echo $now_year[29]; ?>>29</option>
		<option value="30"<?php echo $now_year[30]; ?>>30</option>
		<option value="31"<?php echo $now_year[31]; ?>>31</option>
		<option value="32"<?php echo $now_year[32]; ?>>32</option>
		<option value="33"<?php echo $now_year[33]; ?>>33</option>
		<option value="34"<?php echo $now_year[34]; ?>>34</option>
		<option value="35"<?php echo $now_year[35]; ?>>35</option>
		<option value="36"<?php echo $now_year[36]; ?>>36</option>
		<option value="37"<?php echo $now_year[37]; ?>>37</option>
		<option value="38"<?php echo $now_year[38]; ?>>38</option>
		<option value="39"<?php echo $now_year[39]; ?>>39</option>
		<option value="40"<?php echo $now_year[40]; ?>>40</option>
		<option value="41"<?php echo $now_year[41]; ?>>41</option>
		<option value="42"<?php echo $now_year[42]; ?>>42</option>
		<option value="43"<?php echo $now_year[43]; ?>>43</option>
		<option value="44"<?php echo $now_year[44]; ?>>44</option>
		<option value="45"<?php echo $now_year[45]; ?>>45</option>
		<option value="46"<?php echo $now_year[46]; ?>>46</option>
		<option value="47"<?php echo $now_year[47]; ?>>47</option>
		<option value="48"<?php echo $now_year[48]; ?>>48</option>
		<option value="49"<?php echo $now_year[49]; ?>>49</option>
		<option value="50"<?php echo $now_year[50]; ?>>50</option>
		<option value="51"<?php echo $now_year[51]; ?>>51</option>
		<option value="52"<?php echo $now_year[52]; ?>>52</option>
		<option value="53"<?php echo $now_year[53]; ?>>53</option>
		<option value="54"<?php echo $now_year[54]; ?>>54</option>
		<option value="55"<?php echo $now_year[55]; ?>>55</option>
		<option value="56"<?php echo $now_year[56]; ?>>56</option>
		<option value="57"<?php echo $now_year[57]; ?>>57</option>
		<option value="58"<?php echo $now_year[58]; ?>>58</option>
		<option value="59"<?php echo $now_year[59]; ?>>59</option>
		<option value="60"<?php echo $now_year[60]; ?>>60</option>
		<option value="61"<?php echo $now_year[61]; ?>>61</option>
		<option value="62"<?php echo $now_year[62]; ?>>62</option>
		<option value="63"<?php echo $now_year[63]; ?>>63</option>
		<option value="64"<?php echo $now_year[64]; ?>>64</option>
		<option value="65"<?php echo $now_year[65]; ?>>65</option>
		<option value="66"<?php echo $now_year[66]; ?>>66</option>
		<option value="67"<?php echo $now_year[67]; ?>>67</option>
		<option value="68"<?php echo $now_year[68]; ?>>68</option>
		<option value="69"<?php echo $now_year[69]; ?>>69</option>
		<option value="70"<?php echo $now_year[70]; ?>>70</option>
		<option value="71"<?php echo $now_year[71]; ?>>71</option>
		<option value="72"<?php echo $now_year[72]; ?>>72</option>
		<option value="73"<?php echo $now_year[73]; ?>>73</option>
		<option value="74"<?php echo $now_year[74]; ?>>74</option>
		<option value="75"<?php echo $now_year[75]; ?>>75</option>
		<option value="76"<?php echo $now_year[76]; ?>>76</option>
		<option value="77"<?php echo $now_year[77]; ?>>77</option>
		<option value="78"<?php echo $now_year[78]; ?>>78</option>
		<option value="79"<?php echo $now_year[79]; ?>>79</option>
		<option value="80"<?php echo $now_year[80]; ?>>80</option>
		<option value="81"<?php echo $now_year[81]; ?>>81</option>
		<option value="82"<?php echo $now_year[82]; ?>>82</option>
		<option value="83"<?php echo $now_year[83]; ?>>83</option>
		<option value="84"<?php echo $now_year[84]; ?>>84</option>
		<option value="85"<?php echo $now_year[85]; ?>>85</option>
		<option value="86"<?php echo $now_year[86]; ?>>86</option>
		<option value="87"<?php echo $now_year[87]; ?>>87</option>
		<option value="88"<?php echo $now_year[88]; ?>>88</option>
		<option value="89"<?php echo $now_year[89]; ?>>89</option>
		<option value="90"<?php echo $now_year[90]; ?>>90</option>
		<option value="91"<?php echo $now_year[91]; ?>>91</option>
		<option value="92"<?php echo $now_year[92]; ?>>92</option>
		<option value="93"<?php echo $now_year[93]; ?>>93</option>
		<option value="94"<?php echo $now_year[94]; ?>>94</option>
		<option value="95"<?php echo $now_year[95]; ?>>95</option>
		<option value="96"<?php echo $now_year[96]; ?>>96</option>
		<option value="97"<?php echo $now_year[97]; ?>>97</option>
		<option value="98"<?php echo $now_year[98]; ?>>98</option>
		<option value="99"<?php echo $now_year[99]; ?>>99</option>
		</select>年

		<select class="upload_month_select" name="upload_month_select" onChange="last_day_func('upload')">
		<option value="01"<?php echo $now_month[1]; ?>>1</option>
		<option value="02"<?php echo $now_month[2]; ?>>2</option>
		<option value="03"<?php echo $now_month[3]; ?>>3</option>
		<option value="04"<?php echo $now_month[4]; ?>>4</option>
		<option value="05"<?php echo $now_month[5]; ?>>5</option>
		<option value="06"<?php echo $now_month[6]; ?>>6</option>
		<option value="07"<?php echo $now_month[7]; ?>>7</option>
		<option value="08"<?php echo $now_month[8]; ?>>8</option>
		<option value="09"<?php echo $now_month[9]; ?>>9</option>
		<option value="10"<?php echo $now_month[10]; ?>>10</option>
		<option value="11"<?php echo $now_month[11]; ?>>11</option>
		<option value="12"<?php echo $now_month[12]; ?>>12</option>
		</select>月

		<select class="upload_day_select" name="upload_day_select">
		<option value="01"<?php echo $now_day[1]; ?>>1</option>
		<option value="02"<?php echo $now_day[2]; ?>>2</option>
		<option value="03"<?php echo $now_day[3]; ?>>3</option>
		<option value="04"<?php echo $now_day[4]; ?>>4</option>
		<option value="05"<?php echo $now_day[5]; ?>>5</option>
		<option value="06"<?php echo $now_day[6]; ?>>6</option>
		<option value="07"<?php echo $now_day[7]; ?>>7</option>
		<option value="08"<?php echo $now_day[8]; ?>>8</option>
		<option value="09"<?php echo $now_day[9]; ?>>9</option>
		<option value="10"<?php echo $now_day[10]; ?>>10</option>
		<option value="11"<?php echo $now_day[11]; ?>>11</option>
		<option value="12"<?php echo $now_day[12]; ?>>12</option>
		<option value="13"<?php echo $now_day[13]; ?>>13</option>
		<option value="14"<?php echo $now_day[14]; ?>>14</option>
		<option value="15"<?php echo $now_day[15]; ?>>15</option>
		<option value="16"<?php echo $now_day[16]; ?>>16</option>
		<option value="17"<?php echo $now_day[17]; ?>>17</option>
		<option value="18"<?php echo $now_day[18]; ?>>18</option>
		<option value="19"<?php echo $now_day[19]; ?>>19</option>
		<option value="20"<?php echo $now_day[20]; ?>>20</option>
		<option value="21"<?php echo $now_day[21]; ?>>21</option>
		<option value="22"<?php echo $now_day[22]; ?>>22</option>
		<option value="23"<?php echo $now_day[23]; ?>>23</option>
		<option value="24"<?php echo $now_day[24]; ?>>24</option>
		<option value="25"<?php echo $now_day[25]; ?>>25</option>
		<option value="26"<?php echo $now_day[26]; ?>>26</option>
		<option value="27"<?php echo $now_day[27]; ?>>27</option>
		<option value="28"<?php echo $now_day[28]; ?>>28</option>
		<option value="29"<?php echo $now_day[29]; ?>>29</option>
		<option value="30"<?php echo $now_day[30]; ?>>30</option>
		<option value="31"<?php echo $now_day[31]; ?>>31</option>
		</select>日
		<br>
		<button type="button" class="form_button" name="upload_date_submit" onclick="step1_submit()">決定</button>
		<button type="button" class="form_button" onclick="history.back()">キャンセル</button>
	</form>
	</div>
	<!-- 撮影日を選択するフォームの箇所ここまで -->

	<!-- アップロードするファイルを選択するフォームの箇所 -->
	<div id="step2_field">
	<p class="passive">step.2 写真の選択</p>
	<form id="file_select_form" name="file_select_form" method="post" enctype="multipart/form-data">
		<label class="passive">ファイルを選択<input type="file" id="passive" name="upfile[]" accept="image/*" multiple onclick="upfile_clear()" onChange="show_thumbnail()"></label>
		<button type="button" class="passive" name="file_select_submit" onclick="step2_submit()">決定</button>
		<button type="button" class="passive" name="file_select_return" onclick="step2_return()">戻る</button>
	</form>
	<span class="passive"></span>
	<!-- サムネイルを表示するフィールド -->
	<div id="passive_thumbnail_field"></div>
	<!-- サムネイルのテンプレート置き場 -->
	<div id="thumbnail_template_field">
		<!-- このdivはjavascriptでコピーするテンプレート -->
		<div class="passive">
			<form class="passive" name="thumbnail_form_0">
				<button type="button" class="passive" onclick="form_change(this)" disabled><img src=""></button>
				<input type="hidden" value="">
			</form>
		</div>
	</div>
	</div>
	<!-- アップロードするファイルを選択するフォームの箇所ここまで -->

	<div class="step3_base"></div><!-- step3_fieldが上に乗っかる領域 -->

	<!-- 登録情報を入力するフィールド -->
	<div id="step3_field">
	<p class="passive">step.3 登録情報の入力</p>
	<form class="passive" name="start_num_form" method="post" onsubmit="return false;">
		開始番号:
		<input type="tel" class="start_num_input" name="start_num_input" maxlength='4' value="" onChange="start_num_change(this)">
		<br><span class="passive"></span>
	</form>
	<!-- 登録情報を入力するフォームを内包するフィールド -->
	<div class="passive"></div>
	<!-- フォームのテンプレート置き場 -->
	<div class="info_template_field">
		<form class="info_form" name="info_form_template" method="post">
			<dl class="data_edit">
				<dt>タイトル:
					<dd><input type="text" class="title_input" name="title_input" maxlength='50' placeholder="No title" value=""></dd>
				</dt>
				<dt>番号:
					<dd>
						<input type="text" class="num_input" name="num_input" value="" readonly>
						<input type="hidden" class="hidden_num" name="hidden_num" value="">
						<button type="button" class="form_button" onclick="num_change(this,'forward')">1つ前へ</button>
						<button type="button" class="form_button" onclick="num_change(this,'back')">1つ後ろへ</button>
					</dd>
				</dt>
				<dt>お気に入り:
					<dd>
						<label><input type="radio" class="stars_select" name="stars_select" value="1" checked>★1</label>
						<label><input type="radio" class="stars_select" name="stars_select" value="2">★2</label>
						<label><input type="radio" class="stars_select" name="stars_select" value="3">★3</label>
						<label><input type="radio" class="stars_select" name="stars_select" value="4">★4</label>
						<label><input type="radio" class="stars_select" name="stars_select" value="5">★5</label>
					</dd>
				</dt>
				<dt>撮影カメラ:
					<dd class="camera_select_dd">
						<select class="camera_form_select" name="camera_form_select" onChange="form_select_func(this)">
						<option value="existing_camera" selected>既存のカメラ名から選択</option>
						<option value="new_camera">新しいカメラ名を入力</option>
						</select>

						<!-- 既存のカメラ名から選択 -->
						<select class="camera_name_select" name="camera_name_select">
						<?php echo $make_camera_name_option; ?>
						</select>

						<!-- 新しいカメラ名を入力 -->
						<input class="passive" type="text" name="camera_name_input" maxlength='30' placeholder="カメラ名">

						<button type="button" class="form_button" name="all_camera_name_button" onclick="all_camera_name_change(this)">全ての写真に反映</button>
					</dd>
				</dt>
				<dt>非表示フラグ:
					<dd>
						<select class="secret_flug_select" name="secret_flug_select">
						<option value="0" selected>OFF</option>
						<option value="1">ON</option>
						</select>
					</dd>
				</dt>
				<dt>メモ:
					<dd>
						<input type="text" class="memo_input" name="memo_input" maxlength='140' value="">
					</dd>
				</dt>
			</dl>
		</form>
	</div>
	<form class="last_form" name="last_form" method="post">
		<button type="button" class="passive" name="last_submit" onclick="step3_submit()">決定</button>
		<button type="button" class="passive" name="last_return" onclick="step3_return()">戻る</button>
	</form>
	</div>
	<!-- 登録情報を入力するフィールドここまで -->
</div>
<!-- -------------- アップロードページ終了 -------------- -->

<!-- -------------- インジケータの要素開始 -------------- -->
<div id="indicator">
	<div class="indicator_base">
		<img src="indicator.gif">
	</div>
</div>
<!-- -------------- インジケータの要素終了 -------------- -->

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