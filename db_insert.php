<?php
//javascriptからフォームの情報をもらったら実行

//エラーがあった場合、画面に表示する。
ini_set("display_errors", 1);
error_reporting(E_ALL);

//セッションの宣言
session_start();

//操作用のユーザでデータベースに接続
$conn = oci_connect("photo_operation","sZ9KXhF4","localhost/IK_Photo_DB");
  if (!$conn) {
      $e = oci_error();
      trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }

//javascriptからもらった値を取得
$filming_date = $_POST['filming_date'];
$film_number = $_POST['film_number'];
$title = $_POST['title'];
$favorites_level = $_POST['favorites_level'];
$form_select = $_POST['form_select'];
$camera_name_or_code = $_POST['camera_name_or_code'];
$secret_flug = $_POST['secret_flug'];
$memo = $_POST['memo'];
$file_name = $_POST['file_name'];
$process_date = $_POST['process_date'];
$file_count = $_POST['file_count'];

//画像本体のパスとサムネイルのパスを変数に格納
$file_pass = "photo_data/" . $_SESSION['user_name'] . "/" . $process_date . "/" . $file_name;
$thumbnail_pass = "photo_data/" . $_SESSION['user_name'] . "/" . $process_date . "/thumbnail/thumbnail_" . $file_name;

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
//やりたいことは行の間に割り込ませること

//今登録しようとしているfilming_date、film_number、ユーザ名で登録されている写真があるか確認
//sql文を作成
$sql = "SELECT COUNT(file_pass) FROM photo_table WHERE filming_date = TO_DATE('" . $filming_date . "') AND film_number = " . $film_number . " AND user_name = '" . $_SESSION['user_name'] . "'";

//SQL文を実行
$stid = oci_parse($conn, $sql);
oci_execute($stid);

//実行結果の配列を$rowへ格納
$row = oci_fetch_array($stid, OCI_NUM);

//情報が同じ写真があるかで分岐
IF($row[0] > 0){
	//あった場合はこれから登録する枚数だけ番号を後ろにずらす
	$sql = "UPDATE photo_table SET film_number = film_number + " . $file_count . " WHERE user_name = '" . $_SESSION['user_name'] . "' AND filming_date = TO_DATE('" . $filming_date . "') AND film_number >= " . $film_number;

	//SQL文を実行
	$stid = oci_parse($conn, $sql);
	oci_execute($stid);
}

//ここからinsert文実行の処理
//insertするsql文を作成
$sql = "INSERT INTO photo_table VALUES((SELECT NVL(MAX(serial_number), 0) + 1 FROM photo_table),'" . $_SESSION['user_name'] . "',TO_DATE('" . $filming_date . "')," . $film_number . ",'" . $title . "'," . $favorites_level . ",'" . $camera_code . "','" . $memo . "','" . $file_pass . "','" . $thumbnail_pass . "'," . $secret_flug . ")";

//SQL文を実行
$stid = oci_parse($conn, $sql);
oci_execute($stid);


//ここから画像ファイルの回転を直す処理
//画像本体のデータを取得
$input = $file_pass;
//画像データをphp上で作成
$image = ImageCreateFromJPEG($input);
//exifデータから回転の情報を取得
$exif_datas = @exif_read_data($input);
IF(isset($exif_datas['Orientation'])){
	$spin_data = $exif_datas['Orientation'];
}else{
	$spin_data = 0;
}

//画像本体の回転の補正
orientationFixedImage($input,$image,$spin_data);

//サムネイル画像のデータを取得
$input = $thumbnail_pass;
//画像データをphp上で作成
$image = ImageCreateFromJPEG($input);
//サムネイル画像の回転の補正
orientationFixedImage($input,$image,$spin_data);

//ここで終わり。以下は全部関数の記述



//画像の方向を正す関数
function orientationFixedImage($input,$image,$spin_data){
    //$image = ImageCreateFromJPEG($input);
    //$exif_datas = @exif_read_data($input);
    //if(isset($exif_datas['Orientation'])){
    if(isset($spin_data)){
          $orientation = $spin_data;
          if($image){
                  // 未定義
                  if($orientation == 0){
                  // 通常
                  }else if($orientation == 1){
                  // 左右反転
                  }else if($orientation == 2){
                        $image = image_flop($image);
                  // 180°回転
                  }else if($orientation == 3){
                        $image = image_rotate($image,180, 0);
                  // 上下反転
                  }else if($orientation == 4){
                        $image = image_flip($image);
                  // 反時計回りに90°回転 上下反転
                  }else if($orientation == 5){
                        $image = image_flip($image);
                        $image = image_rotate($image,90, 0);
                  // 時計回りに90°回転
                  }else if($orientation == 6){
                        $image = image_rotate($image,270, 0);
                  // 時計回りに90°回転 上下反転
                  }else if($orientation == 7){
                        $image = image_flip($image);
                        $image = image_rotate($image,270, 0);
                  // 反時計回りに90°回転
                  }else if($orientation == 8){
                        $image = image_rotate($image,90, 0);
                  }
          }
    }

    //orientationの値が2以上の場合は画像の書き出し
    if($orientation >= 2){
        ImageJPEG($image ,$input);
        return false;
    }
}

//画像の左右反転を直す関数
function image_flop($image){
    // 画像の幅を取得
    $w = imagesx($image);
    // 画像の高さを取得
    $h = imagesy($image);
    // 変換後の画像の生成（元の画像と同じサイズ）
    $destImage = @imagecreatetruecolor($w,$h);
    // 逆側から色を取得
    for($i=($w-1);$i>=0;$i--){
        for($j=0;$j<$h;$j++){
            $color_index = imagecolorat($image,$i,$j);
            $colors = imagecolorsforindex($image,$color_index);
            imagesetpixel($destImage,abs($i-$w+1),$j,imagecolorallocate($destImage,$colors["red"],$colors["green"],$colors["blue"]));
        }
    }
    return $destImage;
}

//上下反転を直す関数
function image_flip($image){
    // 画像の幅を取得
    $w = imagesx($image);
    // 画像の高さを取得
    $h = imagesy($image);
    // 変換後の画像の生成（元の画像と同じサイズ）
    $destImage = @imagecreatetruecolor($w,$h);
    // 逆側から色を取得
    for($i=0;$i<$w;$i++){
        for($j=($h-1);$j>=0;$j--){
            $color_index = imagecolorat($image,$i,$j);
            $colors = imagecolorsforindex($image,$color_index);
            imagesetpixel($destImage,$i,abs($j-$h+1),imagecolorallocate($destImage,$colors["red"],$colors["green"],$colors["blue"]));
        }
    }
    return $destImage;
}

//画像を回転する関数
function image_rotate($image, $angle, $bgd_color){
	$image = imagerotate($image, $angle, $bgd_color, 0);
	return $image;
}
?>