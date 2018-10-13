<?php
//エラーがあった場合、画面に表示する。
ini_set("display_errors", 1);
error_reporting(E_ALL);

//セッションの宣言
session_start();

//操作用のユーザでデータベースに接続
$conn = oci_connect("photo_operation","********","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//javascriptからもらった値を取得
$title = $_POST['title'];
$filming_century = $_POST['filming_century'];
$filming_year = $_POST['filming_year'];
$filming_month = $_POST['filming_month'];
$filming_day = $_POST['filming_day'];
$before_date = $_POST['before_date'];
$film_number = intval($_POST['film_number']);
$before_number = intval($_POST['before_number']);
$stars = $_POST['stars'];
$form_select = $_POST['form_select'];
$camera_name_or_code = $_POST['camera_name_or_code'];
$before_camera_code = $_POST['before_camera_code'];
$secret_flug = $_POST['secret_flug'];
$memo = $_POST['memo'];


//ここからカメラ名テーブルの処理
//やりたいことは新しいカメラ名が入力されていたらそれをカメラ名テーブルに登録すること

//新規のカメラ名を入力していて、かつカメラ名が空白かどうか確認
IF($form_select == "new_camera" && $camera_name_or_code == ""){
	//既存のカメラ名を選んでいた場合に変更する(この処理をはさまないとcamera_tableに空白のカメラ名が登録できてしまう)
	$form_select = "existing_camera";
}

//$form_selectの値によって分岐
IF($form_select == "existing_camera"){
	//既存のカメラ名を選んでいた場合
	$camera_code = $camera_name_or_code;

}elseif($form_select == "new_camera"){
	//新規のカメラ名を入力していた場合既存のカメラ名と被っているか確認
	//sql文を作成
	$sql = "SELECT camera_code,camera_name FROM camera_table WHERE user_name = '" . $_SESSION['user_name'] . "' AND camera_name = '" . $camera_name_or_code . "'";

	//SQL文を実行し、実行結果を$stidに格納
	$stid = oci_parse($conn, $sql);
	oci_execute($stid);

	//実行結果の配列を$rowへ格納
	$row = oci_fetch_array($stid, OCI_NUM);

	//結果によって分岐
	IF(isset($row[0])){
		//かぶっているものがあった場合$camera_codeにコード番号を入れる
		$camera_code = $row[0];

	}else{
		//かぶっているものがなかった場合camera_tableに新しくカメラ名を登録する
		//まず現在のユーザでの$camera_nameの最大値を取り出す
		$sql = "SELECT camera_code FROM camera_table WHERE user_name = '" . $_SESSION['user_name'] . "' ORDER BY camera_code DESC FETCH FIRST 1 ROWS ONLY";

		//SQL文を実行し、実行結果を$stidに格納
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);

		//実行結果の配列を$rowへ格納
		$row = oci_fetch_array($stid, OCI_NUM);

		IF(isset($row[0])){
			//$camera_codeに取得した最大値に+1の値を格納
			$camera_code = intval($row[0]) + 1;
		}else{
			//取得できなかった場合(camera_tableの登録数が0の場合)は1を指定
			$camera_code = 1;
		}
		
		//sql文を作成
		$sql = "INSERT INTO camera_table VALUES((SELECT NVL(MAX(serial_number), 0) + 1 FROM camera_table),'" . $_SESSION['user_name'] . "'," . $camera_code . ",'" . $camera_name_or_code . "')";

		//SQL文を実行し、実行結果を$stidに格納
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);

	}
}

//ここから連番の処理
//やりたいことは行の間に割り込ませること、空行ができないよう最後の行に入れること

//撮影日の変数を連結する
$filming_full_date = $filming_century . $filming_year . "/" . $filming_month . "/" . $filming_day;

//撮影日と番号のどちらかが変更される場合は処理を続ける
IF($before_date !== $filming_full_date OR $before_number !== $film_number){

//変更後の日付の中でのfilm_numberの最大値を取得
//sql文を作成
$sql = "SELECT film_number FROM photo_table WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $filming_full_date . "') ORDER BY film_number DESC FETCH FIRST 1 ROWS ONLY";
//SQL文を実行し、実行結果を$stidに格納
$stid = oci_parse($conn, $sql);
oci_execute($stid);
//実行結果の配列を$rowへ格納
$row = oci_fetch_array($stid, OCI_NUM);
//変更後の日付のfilm_numberの最大値を記録
$new_days_max_number = intval($row[0]);

//編集対象のfilm_numberを一時的に10000に変更
//sql文を作成
$sql = "UPDATE photo_table SET film_number = 10000 WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $before_date . "') AND film_number = " . $before_number;
//SQL文を実行し、実行結果を$stidに格納
$stid = oci_parse($conn, $sql);
oci_execute($stid);

//変更前の番号を$emptied_numberに記録
$emptied_number = $before_number;
$before_number = 10000;

//現行と変更後のfilming_dateが同じかどうかで分岐
IF($before_date == $filming_full_date){
	//ここから行を開ける処理、もしくは行を詰める処理
	//filming_dateが同じ場合は現行と変更後のfilm_numberのどちらが大きいか、またfilm_numberと最大値の比較によって分岐
	IF($emptied_number > $film_number){
		//現行の方が大きい場合は変更後～現行の間のflim_numberを+1する
		$sql = "UPDATE photo_table SET film_number = film_number + 1 WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $filming_full_date . "') AND film_number >= " . $film_number . " AND film_number <= " . $emptied_number;

	}elseif($emptied_number < $film_number && $film_number <= $new_days_max_number){
		//変更後の方が大きい場合、かつfilm_numberが最大値以下の場合は現行～変更後の間のflim_numberを-1する
		$sql = "UPDATE photo_table SET film_number = film_number - 1 WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $filming_full_date . "') AND film_number >= " . $emptied_number . " AND film_number <= " . $film_number;

	}elseif($emptied_number < $film_number && $film_number > $new_days_max_number){
		//変更後の方が大きい場合、かつfilm_numberが最大値よりも大きい場合は現行～最大値の間のflim_numberを-1して
		//その後film_numberを最大値と同じにする
		$sql = "UPDATE photo_table SET film_number = film_number - 1 WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $filming_full_date . "') AND film_number >= " . $emptied_number . " AND film_number <= " . $new_days_max_number;
		$film_number = $new_days_max_number;

	}
	//分岐で作成したSQL文を実行
	$stid = oci_parse($conn, $sql);
	oci_execute($stid);

}else{
	//filming_dateが違う場合、変更したい番号と対象の日付の最後の番号のどちらが大きい、もしくは変更したい日付の最大値がnullかで分岐
	IF(!$new_days_max_number){
		//変更したい日付の最大値がnullだった場合はflim_numberを1に指定する
		$film_number = 1 ;

	}elseif($film_number > $new_days_max_number){
		//変更したい番号の方が大きい場合はflim_numberを最大値+1にする
		$film_number = $new_days_max_number + 1 ;

	}elseif($film_number <= $new_days_max_number){
		//最大値の方が大きい場合は対象の日付で変更後～最後の間のflim_numberを+1する
		$sql = "UPDATE photo_table SET film_number = film_number + 1 WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $filming_full_date . "') AND film_number >= " . $film_number . " AND film_number <= " . $new_days_max_number;
		//SQL文を実行(他の分岐では行を開ける必要がないのでここで実行する)
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);
	}
}
//撮影日と番号のどちらも変更されない場合はここまでスキップ
}

//フォームの入力内容を反映させるsql文の作成
$sql = "UPDATE photo_table SET filming_date = TO_DATE('" . $filming_full_date . "'), film_number = " . $film_number . ", title = '" . $title . "', favorites_level = " . $stars . ", camera_code = '" . $camera_code . "', memo = '" . $memo . "', secret_flug = " . $secret_flug . " WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $before_date . "') AND film_number = " . $before_number;
//SQL文を実行
$stid = oci_parse($conn, $sql);
oci_execute($stid);

//変更前と変更後のfilming_dateが同じかどうかで分岐
IF($before_date == $filming_full_date){
	//filming_dateが同じ場合は何もしない

}else{
	//以下filming_dateが違う場合
	//まず変更前の日付の最後の番号を取得する
	$sql = "SELECT film_number FROM photo_table WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $before_date . "') ORDER BY film_number DESC FETCH FIRST 1 ROWS ONLY";

	//SQL文を実行し、実行結果を$stidに格納
	$stid = oci_parse($conn, $sql);
	oci_execute($stid);
	//実行結果の配列を$rowへ格納
	$row = oci_fetch_array($stid, OCI_NUM);
	//変更後の日付のfilm_numberの最大値を記録
	$old_days_max_number = intval($row[0]);

	//変更前の番号と変更前の日付の最後の番号のどちらが大きいか、もしくは元々の日付の最後の番号がnullかで分岐
	IF(!$old_days_max_number){
		//変更前の日付の最後の番号がnullは何もしない

	}elseif($emptied_number > $old_days_max_number){
		//変更前の番号が大きい場合は何もしない

	}elseif($emptied_number < $old_days_max_number){
		//変更前の日付の最後の番号が大きい場合は、変更前の番号～最後の間のflim_numberを-1する
		$sql = "UPDATE photo_table SET film_number = film_number - 1 WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $before_date . "') AND film_number >= " . $emptied_number . " AND film_number <= " . $old_days_max_number;
		//SQL文を実行(他の分岐では行を詰める必要がないのでここで実行する)
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);
	}
}

//同じcamera_codeを持つレコードが0件になった時、そのcamera_codeを削除する処理
//変更前のcamera_codeが空白かどうか確認
IF($before_camera_code == ""){
	//空白だったら処理しない

}else{
	//空白でなければcamera_codeを変更しているか確認
	IF($before_camera_code == $camera_code){
		//変更していなかったら処理しない

	}else{
		//変更していたら変更前と同じcamera_codeのレコードの件数を数える
		//まずsql文を作成する
		$sql = "SELECT COUNT(file_pass) FROM photo_table WHERE user_name = '" . $_SESSION['user_name'] . "' AND camera_code = " . $before_camera_code;
		//SQL文を実行
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);
		//実行結果の配列を$rowへ格納
		$row = oci_fetch_array($stid, OCI_NUM);
		//レコードの件数を変数に格納
		$rows_count = intval($row[0]);

		//変更前と同じcamera_codeのレコードがあるかどうかで分岐
		IF($rows_count > 0){
			//ある場合は処理しない

		}else{
			//無かった場合は変更前のcamera_codeをcamera_tableから削除する
			//まずsql文を作成する
			$sql = "DELETE FROM camera_table WHERE user_name = '" . $_SESSION['user_name'] . "' AND camera_code = " . $before_camera_code;
			//SQL文を実行
			$stid = oci_parse($conn, $sql);
			oci_execute($stid);
		}
	}
}

//おわり
?>