document.getElementsByClassName('user_setting_submit').item(0).onclick = function(){

//フォームの要素から値を取得
var tmp_slide_speed = document.getElementsByClassName('slide_speed_select');
var tmp_secret_status = document.getElementsByClassName('secret_status_check');
var tmp_now_password = document.getElementsByClassName('now_password_input');
var tmp_new_password = document.getElementsByClassName('new_password_input');
var tmp_password_agein = document.getElementsByClassName('password_agein_input');
//tmpにオブジェクトを格納し、そのあとtmpから値、チェックのtrue,falseを変数に格納
slide_speed = tmp_slide_speed.item(0).value;
secret_status = tmp_secret_status.item(0).checked;
now_password = tmp_now_password.item(0).value;
new_password = tmp_new_password.item(0).value;
password_agein = tmp_password_agein.item(0).value;

//パスワードの条件に引っかかった時の表示をリセット
document.getElementsByClassName('cant_use_pass').item(0).textContent = "";
document.getElementsByClassName('password_mismatch').item(0).textContent = "";

//スライドと非表示設定のところは無条件で変更可能
//パスワードを変更する場合は条件をクリアしたら処理へ行けるようにする

if(now_password.length > 0 || new_password.length > 0 || password_agein.length > 0){

	//パスワード入力欄のどれかに文字が入っていたらパスワード設定の判定へ
	//新しいパスワードと再入力のパスワードがあっているか判定
	if(new_password == password_agein){
		//一致していたら処理続行
		//alert("パスワード一致");

		//文字数が4文字以上かどうか判定
		if(new_password.length >= 4){
			//4文字以上なら処理続行
			//alert("4文字以上です");

			//文字数が20文字以下かどうか判定
			if(new_password.length <= 20){
				//20文字以下なら処理続行
				//alert("20文字以下です");

				//新パスワードから半角英数字のみ取り出す
				var char_check = new_password.match(/[A-Za-z0-9]*/);
				//alert(char_check);
				//取り出した文字列と新パスワードの文字列が同一か確認
				//これにより全角文字があるかどうかが確認できる
				if(char_check == new_password){
					//alert("半角文字だけです");
					var pass_change_flug = "true";
					var func_stop_flug = "false";
				}else{
					//全角文字が1文字でもあったらここへ
					//全角文字があったらメッセージを出して処理終了
					document.getElementsByClassName('cant_use_pass').item(0).textContent = "半角英数字以外はパスワードに使用できません。";
					var func_stop_flug = "true";
				}

			}else{
				//文字数が20文字以上だったらここへ
				//多かったらメッセージを出して処理終了
				document.getElementsByClassName('cant_use_pass').item(0).textContent = "新しいパスワードが20文字以上です。";
				var func_stop_flug = "true";
			}
		}else{
			//文字数が4文字以下だったらここへ
			//少なかったらメッセージを出して処理終了
			document.getElementsByClassName('cant_use_pass').item(0).textContent = "新しいパスワードが4文字以下です。";
			var func_stop_flug = "true";
		}
	}else{
		//新しいパスワードと再入力のパスワードが合っていなかったらここへ
		//メッセージを出して処理終了
		document.getElementsByClassName('cant_use_pass').item(0).textContent = "再入力したパスワードが違います";
		var func_stop_flug = "true";
	}

}else{
	//パスワード入力欄全てが空白の場合はここへ
	var pass_change_flug = "false";
	var func_stop_flug = "false";
}

//パスワードを変更しない場合、パスワードを変更する条件をすべて満たしている場合は
//phpでの処理に移る
if(func_stop_flug == "true"){
	//フラグがtrue(パスワードの条件を満たさなかった)の場合はここで終了
	//alert("処理しません");

}else{
	//フラグがfalseの場合は処理を進める。
	//alert("処理します");
	
	//phpに値を渡して処理を実施
	//正常に通信ができた場合は以下へ
	php_func(slide_speed, secret_status, now_password, new_password, pass_change_flug).done(function(php_back){
		//現在のパスワードがあっているか判定
		//phpからの戻り値が"password_mismatch"の場合は現在のパスワードが間違っている
		if(php_back == "password_mismatch"){
			//間違っていたらメッセージを出して処理終了
			document.getElementsByClassName('password_mismatch').item(0).textContent = "現在のパスワードが違います";
		}else if(php_back == "success"){
			//問題なければ完了のメッセージを表示、その後リロードして処理終了
			alert("設定を変更しました。");
			location.reload();

		}
	//正常に通信ができなかった場合はメッセージを表示して処理終了
	}).fail(function(php_back){
		alert("正常に通信ができませんでした。");

	});

}

}

function php_func(slide_speed, secret_status, now_password, new_password, pass_change_flug){
return $.ajax({
    type: 'POST',
    url: 'user_setting.php',
    data: {
	  'slide_speed' : slide_speed,
	  'secret_status' : secret_status,
	  'now_password' : now_password,
	  'new_password' : new_password,
	  'pass_change_flug' : pass_change_flug
	  }
})
}