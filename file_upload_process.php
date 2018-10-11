<?php
//javascriptからファイル情報をもらったら実行
//(もらう情報はファイル情報)

//エラーがあった場合、画面に表示する。
ini_set("display_errors", 1);
error_reporting(E_ALL);

//セッションの宣言
session_start();

//ユーザー名のフォルダがあるか確認する処理
//ユーザのフォルダパスを変数に格納
$user_folder = "/var/www/html/photo_data/" . $_SESSION['user_name'];

if(!(file_exists($user_folder))){
	//なければフォルダを作成
	mkdir($user_folder,0777);
}

//今の日付のフォルダがあるか確認する処理
$process_date = date("Ymd");
//当日のフォルダパスを変数に格納
$date_folder = $user_folder . "/" . $process_date;
//サムネイルを入れるフォルダパスを変数に格納
$thumbnail_folder = $date_folder . "/thumbnail";

if(!(file_exists($date_folder))){
	//なければフォルダを作成
	mkdir($date_folder,0777);
	mkdir($thumbnail_folder,0777);
}

//ファイルをアップロードする処理
//先にjavascriptに返す重複しないファイル名を入れる変数を宣言
$java_return_name = "";

//写真の数だけ繰り返し
for($i = 0 ; $i < count($_FILES["upfile"]["tmp_name"]) ; $i++){
	//is_uploaded_file関数でHTTP POST でアップロードされたファイルかどうかを調べる(不正な処理を防ぐ記述?)
	if (is_uploaded_file($_FILES["upfile"]["tmp_name"][$i])){

		//ここから重複した名前を回避する処理
		//まずサーバに置くファイル名(フルパス)を変数に格納
		$full_file_name = $date_folder . "/" . $_FILES["upfile"]["name"][$i];
		//ファイル名確認用の変数
		$check_file_name = $date_folder . "/" . $_FILES["upfile"]["name"][$i];

		//同名のファイルが存在するか確認(forで同名のファイルが存在する間くりかえし)
		for($ii = 1; file_exists($check_file_name) and $ii < 99; $ii++){
			//同名のファイルがあったらファイル名を変更
			//まずファイルパス、名前等の情報を連想配列で取得
			$info = pathinfo($full_file_name);
			//フォルダとファイル名と数字をくっつけた変数を格納
			$filepath = $info['dirname'] . "/" . $info['filename'] . "_" . $ii;
			//連想配列内に拡張子が格納されていたら末尾にくっつける
			if(isset($info['extension'])){
				$check_file_name = $filepath . "." . $info['extension'];
			}
		}
		//重複していない名前をファイル名に指定
		$full_file_name = $check_file_name;

		//javascriptに返す重複しないファイル名を変数に入れる
		$info = pathinfo($full_file_name);
		$file_name = $info['filename'];
		if(isset($info['extension'])){
			$file_name = $file_name . "." . $info['extension'];
		}
		$java_return_name = $java_return_name . "," . $file_name;

		//ここからファイルをアップロードする処理
		if (move_uploaded_file($_FILES["upfile"]["tmp_name"][$i], $full_file_name)){
			//アップロード成功したらファイルのパーミッションを変更する
			chmod($full_file_name, 0644);
		}else{
			//失敗したら途中で処理を終了する
			echo $_FILES["upfile"]["name"][$i] . ":アップロードエラー";
			exit();
		}

		//ここからサムネイルを保存する処理
		//まずアップロードしたファイルをフルパスで取得
		$thumbnail_file = $full_file_name;
		//サムネイルにつける名前をフルパスで格納
		$thumbnail_full_path = $thumbnail_folder . "/thumbnail_" . $file_name;

		//元画像ファイルを読み込む
		$in = ImageCreateFromJPEG($thumbnail_file);
		//画像の幅と高さを取得
		$width = ImageSx($in);
        	$height = ImageSy($in);

		//幅と高さの最低サイズを指定
		$min_width = 220;
		$min_height = 220;

		//画像のサイズによって分岐
                if($width == $height){
			//正方形の場合は220×220にする
			$new_width = $min_width;
			$new_height = $min_height;
                }else if($width > $height){
			//横長の場合は比率そのままで横幅を220pxにする
			$new_width = $min_width;
			$new_height = $height*($min_width/$width);
                }else if($width < $height){
			//縦長の場合は比率そのままで高さを220pxにする
			$new_width = $width*($min_height/$height);
			$new_height = $min_height;
                }

		//リサイズした画像を生成して格納
		$out = ImageCreateTrueColor($new_width , $new_height);
		ImageCopyResampled($out, $in,0,0,0,0, $new_width, $new_height, $width, $height);
		ImageJPEG($out, $thumbnail_full_path);

	}else{
		echo $_FILES["upfile"]["name"][$i] . 'の処理中に\nis_uploaded_file関数でfalseが返されました。';
		exit();
	}
}

//$java_return_nameの先頭の1文字を削除
$java_return_name = substr($java_return_name , 1 , strlen($java_return_name)-1);

//javascriptに重複しないファイル名を返して終了
echo $java_return_name;

?>