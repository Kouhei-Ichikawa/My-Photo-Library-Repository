document.editing_form.editing_page_submit.onclick = function(){

//フォームの要素から値を取得
var title = document.editing_form.title_input.value;
var filming_century = document.editing_form.filming_century_select.value;
var filming_year = document.editing_form.filming_year_select.value;
var filming_month = document.editing_form.filming_month_select.value;
var filming_day = document.editing_form.filming_day_select.value;
var before_date = document.editing_form.before_date.value;
var film_number = document.editing_form.film_number_input.value;
var before_number = document.editing_form.before_number.value;
//stars_selectのラジオボタンからcheckedになっているもののvalueを取得
var stars = $('input[name="stars_select"]:checked').val();
var form_select = document.editing_form.form_select.value;
if(form_select == "existing_camera"){
	//「既存のカメラ名から選択」が選ばれていたらプルダウンメニューから値を取得
	var camera_name_or_code = document.editing_form.camera_name_select.value;
}else if(form_select == "new_camera"){
	//「新しいカメラ名を入力」が選ばれていたらテキストボックスから値を取得
	var camera_name_or_code = document.editing_form.camera_name_input.value;
}
var before_camera_code = document.editing_form.before_camera_code.value;
var secret_flug = document.editing_form.secret_flug_select.value;
var memo = document.editing_form.memo_input.value;

//入力規則判定のデフォルト値を設定
var go_sign = "OK";

//エラーメッセージを表示していたら消す
document.getElementsByClassName('error_message').item(0).textContent = "";

//titleの文字数は20文字までの制限をフォームの方でかけている
//titleが空白のものは空白のままで登録
//撮影日を無効な日付(02/31など)にしない対策は入力フォームで対処済み

//チェックの為の変数を作る
var num_check = "" + film_number;
var num_check = Number(num_check.match(/[0-9]*/));

//film_numberの文字が数字だけかどうか確認(数字以外が含まれていたら二つが一致しない)
if(film_number != num_check){
	//数字以外が含まれていたら入力規則判定をNGにしてメッセージを表示
	var go_sign = "NG";
	document.getElementsByClassName('error_message').item(0).textContent = "番号には1以上の数字を入力してください。";
}

//film_numberは整数の数字か確認
if(film_number.match(/^-?[0-9]+\.[0-9]+$/)){
	//小数を含む数値の場合、入力規則判定をNGにしてメッセージを表示
	var go_sign = "NG";
	document.getElementsByClassName('error_message').item(0).textContent = "番号には1以上の数字を入力してください。";
}

//film_numberは1以上の数字かどうかか確認
if(film_number < 1){
	//範囲外の数値の場合、入力規則判定をNGにしてメッセージを表示
	var go_sign = "NG";
	document.getElementsByClassName('error_message').item(0).textContent = "番号には1以上の数字を入力してください。";
}

//film_numberが空白でないか確認
if(film_number == ""){
	//空白だったら入力規則判定をNGにしてメッセージを表示
	var go_sign = "NG";
	document.getElementsByClassName('error_message').item(0).textContent = "番号を入力してください。";
}

//新しいカメラ名の文字数は30文字までの制限をフォームの方でかけている
//入力した新しいカメラ名が既存のものと同じかどうかはphpで確認する
//メモの文字数は140文字までの制限をフォームの方でかけている

//入力規則を確認後、全て問題なかった場合phpに処理を渡す
if(go_sign == "OK"){
	//入力規則が問題なかったらphpに処理を渡す
	php_func(title,filming_century,filming_year,filming_month,filming_day,before_date,film_number,before_number,stars,form_select,camera_name_or_code,before_camera_code,secret_flug,memo).done(function(){
		alert("設定を変更しました。");
		//画像表示ページへ戻って処理終了
		history.back();

	}).fail(function(jqXHR){
		//通信に失敗したらアラートを出して処理終了
		alert("正常に通信ができませんでした。" + jqXHR.status);
	});

}else{
	//javascriptでの入力規則の確認がNGだったら何もせず終了
	//alert("NGでした");

}
//javascriptの本処理終わり
}

//phpに処理を渡す関数
function php_func(title,filming_century,filming_year,filming_month,filming_day,before_date,film_number,before_number,stars,form_select,camera_name_or_code,before_camera_code,secret_flug,memo){
return $.ajax({
    type: 'POST',
    url: 'photo_info_editing.php',
    data: {
	  'title' : title,
	  'filming_century' : filming_century,
	  'filming_year' : filming_year,
	  'filming_month' : filming_month,
	  'filming_day' : filming_day,
	  'before_date' : before_date,
	  'film_number' : film_number,
	  'before_number' : before_number,
	  'stars' : stars,
	  'form_select' : form_select,
	  'camera_name_or_code' : camera_name_or_code,
	  'before_camera_code' : before_camera_code,
	  'secret_flug' : secret_flug,
	  'memo' : memo
	  }
})
}