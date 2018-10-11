//日付を選択して決定ボタンを押したら実行
function step1_submit(){
	//ここからstep1側の表示の設定
	var step1_from = document.upload_date_form;

	//日付選択フォームをdesabledにする
	step1_from.upload_century_select.disabled = true;
	step1_from.upload_year_select.disabled = true;
	step1_from.upload_month_select.disabled = true;
	step1_from.upload_day_select.disabled = true;

	//決定ボタンをdesabledにする
	step1_from.upload_date_submit.disabled = true;

	//ここからstep2側の表示の設定
	var step2_field_div = document.getElementById("step2_field");
	var step2_from = document.file_select_form;

	//「step.2」の文字を表示
	var step2_text = step2_field_div.getElementsByTagName('p');
	step2_text.item(0).className = "step_p";

	//ファイル選択フォームを表示
	var step2_upfile = step2_from.getElementsByTagName('input');
	step2_upfile.item(0).id = "upfile";

	//ファイル選択フォームのラベルを表示
	var step2_label = step2_from.getElementsByTagName('label');
	step2_label.item(0).className = "file_label";

	//決定ボタンを表示
	step2_from.file_select_submit.className = "form_button";
	//戻るボタンを表示
	step2_from.file_select_return.className = "form_button";

	//サムネイルを表示するdiv要素を表示
	var thumbnail_field_div = document.getElementById('passive_thumbnail_field');
	thumbnail_field_div.id = "thumbnail_field";

	//選択した日付にある写真の枚数を取得
	//まず日付選択フォームの値を変数に格納
	var century_value = step1_from.upload_century_select.value;
	var year_value = step1_from.upload_year_select.value;
	var month_value = step1_from.upload_month_select.value;
	var day_value = step1_from.upload_day_select.value;

	//phpで問い合わせるモジュールを実行
	photo_count_php(century_value,year_value,month_value,day_value).done(function(php_return){
		//戻り値をグローバル関数に格納
		photo_count = Number(php_return);
		//連番の表示に使用する数字を宣言
		photo_num = Number(photo_count) + 1;

	}).fail(function(jqXHR){
		//通信に失敗したらアラートを出して処理終了
		alert("正常に通信ができませんでした。" + jqXHR.status);
	});
}

//選択した日付にある写真の枚数を取得するphpを呼ぶ関数
function photo_count_php(century_value,year_value,month_value,day_value){
return $.ajax({
    type: 'POST',
    url: 'select_days_photo_count.php',
    data: {
	   'century_value' : century_value,
	   'year_value' : year_value,
	   'month_value' : month_value,
	   'day_value' : day_value
	  }
})
}

//step.2で戻るボタンを押したら実行
function step2_return(){
	//ここからstep2側の表示の設定
	var step2_field_div = document.getElementById("step2_field");
	var step2_from = document.file_select_form;

	//「step.2」の文字を非表示にする
	var step2_text = step2_field_div.getElementsByTagName('p');
	step2_text.item(0).className = "passive";

	//ファイル選択フォームを非表示にする
	var step2_upfile = step2_from.getElementsByTagName('input');
	step2_upfile.item(0).id = "passive";

	//ファイル選択フォームのラベルを非表示にする
	var step2_label = step2_from.getElementsByTagName('label');
	step2_label.item(0).className = "passive";

	//決定ボタンを非表示にする
	step2_from.file_select_submit.className = "passive";
	//戻るボタンを非表示にする
	step2_from.file_select_return.className = "passive";

	//エラーメッセージのspan要素を非表示にする
	var error_message_span = step2_field_div.getElementsByTagName('span');
	error_message_span.item(0).className = "passive";

	//サムネイルを表示するdiv要素を非表示にする
	var thumbnail_field_div = document.getElementById('thumbnail_field');
	thumbnail_field_div.id = "passive_thumbnail_field";

	//ここからstep1側の表示の設定
	var step1_from = document.upload_date_form;

	//日付選択フォームを有効化する
	step1_from.upload_century_select.disabled = false;
	step1_from.upload_year_select.disabled = false;
	step1_from.upload_month_select.disabled = false;
	step1_from.upload_day_select.disabled = false;

	//決定ボタンを有効化する
	step1_from.upload_date_submit.disabled = false;
}

//サムネイル作成時と情報入力フォーム作成時で使うWorkerオブジェクトを作成
var worker = new Worker('thumbnail_name_loader.js');

//step.2でファイル選択のinputをクリックしたら実行
function upfile_clear(){
	//ファイル選択のフォームをリセットする
	document.file_select_form.reset();

	//エラーメッセージを消す
	var step2_field_div = document.getElementById("step2_field");
	var error_message_span = step2_field_div.getElementsByTagName('span');
	error_message_span.item(0).className = "passive";

	//div要素内に既にサムネイルの要素がある場合はすべて削除する
	var thumbnail_field_div = document.getElementById("thumbnail_field");
	for (var i =thumbnail_field_div.childNodes.length-1; i>=0; i--) {
		thumbnail_field_div.removeChild(thumbnail_field_div.childNodes[i]);
	}
}

//step.2でファイルを選択したら実行
function show_thumbnail(){
	//ファイルを選択したinput要素を変数に格納
	var input_element = document.getElementById("upfile");

	//ファイルが1つ以上選択されているか確認
	if (input_element.files.length > 0) {
		//選択されたファイルの数だけ繰り返し
		for(var i = 0 ; i < input_element.files.length ; i++){
			//ワーカー経由でファイルを読み込む
			worker.postMessage({ "file": input_element.files[i]});
		}

		// Workerから処理が終わったらメッセージを受け取り要素を作る
		worker.onmessage = function (event) {
			//テンプレートのdiv要素をコピーする
			var step2_field_div = document.getElementById("step2_field");
			var thumbnail_template_field_div = document.getElementById("thumbnail_template_field");
			var div_template = thumbnail_template_field_div.getElementsByTagName('div').item(0);
			var new_thumbnail = div_template.cloneNode(true);

			//サムネイルを表示するフィールドを変数に格納
			var thumbnail_field_div = document.getElementById("thumbnail_field");

			//現在サムネイルを表示するフィールドにあるdiv要素の数を数える
			var div_count = thumbnail_field_div.childElementCount;

			//コピーした要素のclass,name,src,hiddenの数字を変更する
			new_thumbnail.className = "thumbnail_frames";	//div要素のクラス
			new_thumbnail.getElementsByTagName('form').item(0).className = "thumbnail_form";	//form要素のクラス
			var new_name = "thumbnail_form_" + div_count;
			new_thumbnail.getElementsByTagName('form').item(0).name = new_name;	//form要素のname
			new_thumbnail.getElementsByTagName('button').item(0).className = "thumbnail_button";	//button要素のクラス
			new_thumbnail.getElementsByTagName('img').item(0).src = event.data.src;	//img要素のsrc(event.data.srcはワーカーからの戻り値)
			new_thumbnail.getElementsByTagName('input').item(0).value = div_count; //hidden要素の数字(サムネイルと情報入力フォームを紐づけるための数字)

			//フィールドにノードを追加する
			thumbnail_field_div.appendChild(new_thumbnail);
		}
	}
}

//ファイルを選択してOKボタンをクリックしたら実行
function step2_submit(){
	//ファイルを選択したinput要素を変数に格納
	var input_element = document.getElementById("upfile");

	//ファイルを選択しているか確認
	if(input_element.files.length > 0 && input_element.files.length <= 100){
		//ファイルが選択されていたら以下へ進む
		//指定した日付の登録枚数+新しい写真 > 9999になるかどうか判定
		if(Number(photo_num) + input_element.files.length > 9999){
			//ボタンの隣にメッセージを表示して終了
			var step2_field_div = document.getElementById("step2_field");
			var error_message_span = step2_field_div.getElementsByClassName('error_message').item(0);
			error_message_span.textContent = "指定した日付の登録可能枚数を超えています。"
		}else{
			//9999枚以下の場合は処理を進める
			//ここからstep2側の表示の設定
			var step2_field_div = document.getElementById("step2_field");
			var step2_from = document.file_select_form;

			//ファイル選択フォームをdesabledにする
			var step2_upfile = step2_from.getElementsByTagName('input');
			step2_upfile.item(0).disabled = true;

			//決定ボタンをdesabledにする
			step2_from.file_select_submit.disabled = true;
			//戻るボタンをdesabledにする
			step2_from.file_select_return.disabled = true;

			//1枚目のサムネイルの背景色を変える
			var first_thumbnail = document.getElementsByClassName('thumbnail_button').item(0);
			first_thumbnail.style.backgroundColor = '#0175cb';

			//サムネイルの画像をクリックできるように設定する
			//まずbutton要素の集まりを取得
			var thumbnail_field_div = document.getElementById("thumbnail_field");
			var thumbnail_button_elements = thumbnail_field_div.getElementsByTagName('button');

			//button要素の数だけ繰り返し
			for(var i = 0 ; i < thumbnail_button_elements.length ; i++){
				//button要素を有効化する
				thumbnail_button_elements.item(i).disabled = false;
			}

			//ここからstep3側の表示の設定
			var step3_field_div = document.getElementById("step3_field");

			//step3_baseを表示する
			var step3_base_div = document.getElementsByClassName("step3_base").item(0);
			step3_base_div.style.display = 'block';

			//「step.3」の文字を表示
			var step3_text = step3_field_div.getElementsByTagName('p');
			step3_text.item(0).className = "step_p";

			//開始番号の設定フォームに値を入れる
			//まず要素を表示させる
			var start_num_form_element = step3_field_div.getElementsByTagName('form').item(0);
			start_num_form_element.className = "start_num_form";
			//値を入れる
			start_num_form_element.start_num_input.value = photo_num;

			//開始番号の隣のエラーメッセージ枠を表示する
			var message_span = step3_field_div.getElementsByTagName('span').item(0);
			message_span.className = "error_message";

			//登録情報を入力するフォームを内包するdiv要素を表示
			var info_field_div = step3_field_div.getElementsByTagName('div').item(0);
			info_field_div.className = "info_field";

			//最後の決定ボタンを表示
			document.last_form.last_submit.className = "form_button";
			//戻るボタンを表示
			document.last_form.last_return.className = "form_button";

			//ファイルを選択したinput要素を変数に格納
			var input_element = document.getElementById("upfile");

			//div要素内に既にform要素がある場合はすべて削除する
			for (var i =info_field_div.childNodes.length-1; i>=0; i--) {
				info_field_div.removeChild(info_field_div.childNodes[i]);
			}

			//選択されたファイルの数だけ繰り返し
			for(var i = 0 ; i < input_element.files.length ; i++){
				//ワーカー経由でファイルを読み込む
				worker.postMessage({ "file": input_element.files[i]});
			}

			//Workerから処理が終わったらメッセージを受け取り要素を作る
			worker.onmessage = function (event) {
				//選択したファイルの数だけフォーム要素をテンプレートからコピーする
				var step3_field_div = document.getElementById("step3_field");
				var info_template_field_div = step3_field_div.getElementsByClassName("info_template_field").item(0);
				var info_form_template = info_template_field_div.getElementsByTagName('form').item(0);
				var new_info_form = info_form_template.cloneNode(true);

				//フォームを表示するフィールドを変数に格納
				var info_field_div = document.getElementsByClassName("info_field").item(0);

				//現在フォームを表示するフィールドにあるform要素の数を数える
				var form_count = info_field_div.childElementCount;

				//1つめのフォームだったら前へボタンを無効にする
				if(form_count == 0){
					var forward_button = new_info_form.getElementsByTagName('button').item(0);
					forward_button.disabled = true;
					//ついでにフォーム要素が見えるように設定する
					new_info_form.style.display = 'block';
				}
				//最後のフォームだったら後ろへボタンを無効にする
				if(form_count == input_element.files.length - 1){
					var back_button = new_info_form.getElementsByTagName('button').item(1);
					back_button.disabled = true;
				}

				//コピーした要素のform name、タイトルの初期値、表示用の番号、サムネイルとフォームを紐づける番号を変更する
				var new_name = "info_form_" + form_count;
				new_info_form.name = new_name; //form要素のname
				new_info_form.title_input.value = event.data.name; //タイトルの初期値(event.data.nameはワーカーからの戻り値)
				new_info_form.num_input.value = Number(photo_num) + Number(form_count); //表示用の番号
				new_info_form.hidden_num.value = form_count; //サムネイルとフォームを紐づける番号

				//フィールドにノードを追加する
				info_field_div.appendChild(new_info_form);
			}

			//フッターの設定
			var footer_div = document.getElementById("footer");
			//画面の最下部に固定する
			footer_div.style.position = 'fixed';
			footer_div.style.bottom = '0px';
			//上にボーダーラインを引く
			footer_div.style.borderTop = 'solid 6px #1f2827';
		}
	}else if(input_element.files.length == 0){
		//ファイルが選択されていなければ以下へ進む
		//ボタンの隣にメッセージを表示して終了
		var step2_field_div = document.getElementById("step2_field");
		var error_message_span = step2_field_div.getElementsByTagName('span');
		error_message_span.item(0).className = "error_message";
		error_message_span.item(0).textContent = "ファイルが選択されていません。"

	}else if(input_element.files.length > 100){
		//ファイルが100枚より多く選択されていいる場合は以下へ進む
		//ボタンの隣にメッセージを表示して終了
		var step2_field_div = document.getElementById("step2_field");
		var error_message_span = step2_field_div.getElementsByTagName('span');
		error_message_span.item(0).className = "error_message";
		error_message_span.item(0).textContent = "選択したファイル数が100を超えています。"
	}
}

//step.3で戻るボタンを押したら実行
function step3_return(){
	//ここからstep3側の表示の設定
	var step3_field_div = document.getElementById("step3_field");

	//step3_baseを非表示にする
	var step3_base_div = document.getElementsByClassName("step3_base").item(0);
	step3_base_div.style.display = 'none';

	//「step.3」の文字を非表示にする
	var step3_text = step3_field_div.getElementsByTagName('p');
	step3_text.item(0).className = "passive";

	//開始番号の設定フォームを非表示にする
	var start_num_form_element = step3_field_div.getElementsByTagName('form').item(0);
	start_num_form_element.className = "passive";

	//開始番号の隣のエラーメッセージ枠を非表示にする
	var message_span = step3_field_div.getElementsByTagName('span').item(0);
	message_span.className = "passive";
	message_span.textContent = "";

	//登録情報を入力するフォームを内包するdiv要素を非表示にする
	var info_field_div = step3_field_div.getElementsByTagName('div');
	info_field_div.item(0).className = "passive";

	//最後の決定ボタンを表示
	document.last_form.last_submit.className = "passive";
	//戻るボタンを表示
	document.last_form.last_return.className = "passive";

	//ここからstep2側の表示の設定
	var step2_field_div = document.getElementById("step2_field");
	var step2_from = document.file_select_form;

	//ファイル選択フォームを有効化する
	var step2_upfile = step2_from.getElementsByTagName('input');
	step2_upfile.item(0).disabled = false;

	//ファイル選択のフォームをリセットする
	step2_from.reset();

	//div要素内に既にサムネイルの要素がある場合はすべて削除する
	var thumbnail_field_div = document.getElementById("thumbnail_field");
	for (var i =thumbnail_field_div.childNodes.length-1; i>=0; i--) {
		thumbnail_field_div.removeChild(thumbnail_field_div.childNodes[i]);
	}

	//決定ボタンを有効化にする
	step2_from.file_select_submit.disabled = false;
	//戻るボタンを有効化にする
	step2_from.file_select_return.disabled = false;

	//フッターの設定
	var footer_div = document.getElementById("footer");
	//画面の最下部に固定する
	footer_div.style.position = 'static';
	footer_div.style.bottom = 'auto';
	//ボーダーラインを消す
	footer_div.style.border = 'none';
}

//開始番号の設定フォームを変更したら実行
function start_num_change(input_element){
	//変更後の番号を取得
	var input_value = input_element.value;

	//数字以外が含まれているかチェックの為の変数を作る
	var num_check = "" + input_value;
	var num_check = Number(num_check.match(/[0-9]*/));

	//メッセージを消しておく
	var step3_field_div = document.getElementById("step3_field");
	var message_span = step3_field_div.getElementsByTagName('span').item(0);
	message_span.textContent = "";

	//入力フォーム内の文字が数字だけかどうか確認(数字以外が含まれていたら二つが一致しない)
	if(input_value != num_check){
		//数字以外が含まれていたらメッセージを表示
		message_span.textContent = "番号には1以上の数字を入力してください。";
		//変更前の数字に戻す
		input_element.value = Number(photo_num);
	}else{
		//変更後の番号が0だった場合は1に直す
		if(Number(input_value) == 0){
			input_element.value = 1;
			var input_value = 1;
		}

		//変更後の番号が指定日に登録されている写真の枚数+1よりも多かった場合は登録されている写真の枚数+1に直す
		if(input_value > Number(photo_count) + 1){
			input_element.value = Number(photo_count) + 1;
			var input_value = Number(photo_count) + 1;
		}

		//変更後の番号 - 元の番号で数字の差を出す
		var num_difference = Number(input_value) - Number(photo_num);
		//photo_numの数字を新しく指定
		photo_num = Number(input_value);

		//0010といった入力をされていた場合の為に直す
		input_element.value = Number(input_value);

		//情報入力フォームの番号に数字の差を足す
		//まずフォームの数を出す
		var info_field_div = document.getElementsByClassName('info_field').item(0);
		var info_form_elements = info_field_div.getElementsByTagName('form');
		var info_form_count = info_form_elements.length;

		//フォームの数だけ繰り返し
		for(var i = 0 ; i < info_form_count ; i++){
			//フォーム内のinput要素の数値を取得
			var num_input_value = info_form_elements.item(i).num_input.value;
			//input要素の数値に数字の差を足す
			var new_num = Number(num_input_value) + Number(num_difference);
			//計算した数字をinput要素の値に設定
			info_form_elements.item(i).num_input.value = new_num;
		}
		alert("設定しました。");
	}
}

//フォームで前後ボタンを押したら実行
function num_change(button_element,form_move){
	//ここからボタンを押したフォームと隣の番号のフォームの番号を変更する処理
	//まずボタンを押したフォームの番号を取得
	var dd_element = button_element.parentNode;
	var view_num = dd_element.getElementsByTagName('input').item(0).value;
	var form_num = dd_element.getElementsByTagName('input').item(1).value;
	//数値型に直す
	var view_num = Number(view_num);
	var form_num = Number(form_num);

	//ボタンを押したフォームの要素を取得
	var form_name = "info_form_" + form_num;
	var form_element = document.getElementsByName(form_name).item(0);

	//ボタンを押したフォームの名前を一時的に変更
	form_element.name = "info_form_tmp";

	//前か後ろのどちらかで分岐
	if(form_move == "forward"){
		//前ボタンを押していたら一つ前のフォームの要素を取得
		var neighbor_form_name = "info_form_" + (form_num - 1);
		var neighbor_form = document.getElementsByName(neighbor_form_name).item(0);

	}else if(form_move == "back"){
		//後ろボタンを押していたら一つ後ろのフォームの要素を取得
		var neighbor_form_name = "info_form_" + (form_num + 1);
		var neighbor_form = document.getElementsByName(neighbor_form_name).item(0);
	}

	//隣のフォームの名前をボタンを押したフォームと同じ名前にする
	//※つまり、名前に±1する
	neighbor_form.name = form_name;

	//隣のフォームのinput(text)要素の番号をボタンを押したフォームの番号にする
	//※textの番号は画面に表示する用の番号
	var neighbor_input = neighbor_form.getElementsByTagName('input').item(1);
	neighbor_input.value = view_num;

	//隣のフォームのinput(hidden)要素の番号をボタンを押したフォームの番号にする
	//※hiddenの番号はサムネイルとフォームを紐づけるための番号
	var neighbor_hidden = neighbor_form.getElementsByTagName('input').item(2);
	neighbor_hidden.value = form_num;

	//前か後ろのどちらかで分岐
	if(form_move == "forward"){
		//前ボタンを押していたらボタンを押したフォームの名前を一つ前の番号に変更する
		form_element.name = "info_form_" + (form_num - 1);

		//ボタンを押したフォームのinput(text)要素の番号を変更する
		var form_input = form_element.getElementsByTagName('input').item(1);
		form_input.value = view_num - 1;

		//ボタンを押したフォームのinput(hidden)要素の番号を変更する
		var form_hidden = form_element.getElementsByTagName('input').item(2);
		form_hidden.value = form_num - 1;

	}else if(form_move == "back"){
		//後ろボタンを押していたらボタンを押したフォームの名前を変更する
		form_element.name = "info_form_" + (form_num + 1);

		//ボタンを押したフォームのinput(text)要素の番号を変更する
		var form_input = form_element.getElementsByTagName('input').item(1);
		form_input.value = view_num + 1;

		//ボタンを押したフォームのinput(hidden)要素の番号を変更する
		var form_hidden = form_element.getElementsByTagName('input').item(2);
		form_hidden.value = form_num + 1;
	}

	//ここからフォームと紐づいているサムネイルの要素を並び替える処理
	//まずボタンを押したフォームのサムネイルの要素の集まりを取得
	var thumbnail_field_div = document.getElementById("thumbnail_field");
	var thumbnail_frames_div = thumbnail_field_div.getElementsByClassName("thumbnail_frames");
	//サムネイルの要素がいくつあるか数える
	var thumbnail_count = thumbnail_frames_div.length;

	//ボタンを押したフォームと紐づいているサムネイルの要素を変数に格納
	var target_thumbnail = thumbnail_frames_div.item(form_num);

	//前か後ろのどちらかで分岐
	if(form_move == "forward"){
		//前ボタンを押した場合、一つ前隣のサムネイルの要素を変数に格納
		var neighbor_thumbnail = thumbnail_frames_div.item(form_num - 1);

		//格納したサムネイルの要素を隣のサムネイルの前に置く
		thumbnail_field_div.insertBefore(target_thumbnail,neighbor_thumbnail);

	}else if(form_move == "back"){
		//後ろボタンを押した場合、一番後ろになるのかで分岐
		if(form_num < (thumbnail_count - 2)){
			//一番後ろの一つ前までになる場合、二つ隣のサムネイルの要素を変数に格納
			var neighbor_thumbnail = thumbnail_frames_div.item(form_num + 2);
			//格納したサムネイルの要素を二つ隣のサムネイルの前に置く
			thumbnail_field_div.insertBefore(target_thumbnail,neighbor_thumbnail);

		}else{
			//一番後ろになる場合、サムネイルの要素の集まりの一番最後に置く
			thumbnail_field_div.appendChild(target_thumbnail);
		}
	}

	//一つ前へ移動の処理の後、一番目になった場合は前へボタンを無効化する
	//まずボタン要素を変数に格納しておく
	var forward_button = dd_element.getElementsByTagName('button').item(0);
	var back_button = dd_element.getElementsByTagName('button').item(1);
	var neighbor_forward_button = neighbor_form.getElementsByTagName('button').item(0);
	var neighbor_back_button = neighbor_form.getElementsByTagName('button').item(1);
	//フォームがいくつあるか数える
	var info_field_div = document.getElementsByClassName("info_field").item(0);
	var forms_count = info_field_div.getElementsByTagName('form').length;
	//一番後ろのフォーム名を格納
	var last_form_name = "info_form_" + (forms_count -1);

	//一旦ボタンを有効化する
	forward_button.disabled = false;
	back_button.disabled = false;

	if(form_move == "forward"){
		if((form_num - 1) == 0){
			//前へボタンを無効化する
			forward_button.disabled = true;
			//後ろへボタンを有効化する
			back_button.disabled = false;

			//となりのフォームの前へボタンを有効化する
			neighbor_forward_button.disabled = false;
		}
	}

	//1つ後ろへ移動の処理の後、一番最後になった場合は後ろへボタンを無効化する
	if(form_move == "back"){
		if((form_num + 1) == (forms_count - 1)){
			//後ろへボタンを無効化する
			back_button.disabled = true;
			//前へボタンを有効化する
			forward_button.disabled = false;

			//となりのフォームの後ろへボタンを有効化する
			neighbor_back_button.disabled = false;
		}
	}

	//一つ前へ移動の結果、隣の要素が一番後ろになった時の処理
	if(neighbor_form.name == last_form_name){
		//となりのフォームの後ろへボタンを無効化する
		neighbor_back_button.disabled = true;
	}

	//一つ後ろへ移動の結果、隣の要素が一番前になった時の処理
	if(neighbor_form.name == "info_form_0"){
		//となりのフォームの前へボタンを無効化する
		neighbor_forward_button.disabled = true;
	}
}

//サムネイルのボタンをクリックしたら実行する関数
function form_change(button_element){
	//ボタンを押したサムネイルの番号を取得
	var form_element = button_element.parentNode;
	var hidden_element = form_element.getElementsByTagName('input').item(0);
	var thumbnail_num = hidden_element.value;

	//thumbnail_field内にあるボタン全部の背景色を黒にする
	var thumbnail_field_div = document.getElementById("thumbnail_field");
	var all_buttons = thumbnail_field_div.getElementsByTagName('button');
	var buttons_count = all_buttons.length;
	//button要素の数だけ繰り返し
	for(var i=0 ; i < buttons_count; i++){
		//背景色を黒にする
		all_buttons.item(i).style.backgroundColor = '#1f2827';
	}

	//info_field内にあるform全部にdisplay: noneを設定する
	//まずフォームの集まりを取得
	var info_field_div = document.getElementsByClassName("info_field").item(0);
	var info_form_elements = info_field_div.getElementsByTagName('form');
	//フォーム要素の数だけ繰り返し
	for(var i=0 ; i < info_form_elements.length ; i++){
		//formを見えなくする
		info_form_elements.item(i).style.display = 'none';
	}

	//押したボタンの背景色を青にする
	var target_button = form_element.getElementsByTagName('button').item(0);
	target_button.style.backgroundColor = '#0175cb';

	//押したサムネイルに紐づいたフォームを可視化する
	var target_form = info_form_elements.item(Number(thumbnail_num));
	target_form.style.display = 'block';
}

//入力したカメラ名をすべてに反映させるボタン
function all_camera_name_change(button_element){
	//カメラ名の入力方法を取得
	var dd_element = button_element.parentNode;
	var form_select = dd_element.getElementsByTagName('select').item(0).value;

	//カメラ名の入力方法によって分岐
	if(form_select == "existing_camera"){
		//既存のカメラ名から選択していたらその値を取得
		var camera_value = dd_element.getElementsByTagName('select').item(1).value;

	}else if(form_select == "new_camera"){
		//新しいカメラ名を入力していたら入力したカメラ名を取得
		var camera_value = dd_element.getElementsByTagName('input').item(0).value;
	}

	//フォーム要素の集まりを取得
	var info_field_div = document.getElementsByClassName("info_field").item(0);
	var info_form_elements = info_field_div.getElementsByTagName('form');

	//フォーム要素の数だけ繰り返し
	for(var i = 0 ; i < info_form_elements.length ; i++){
		//カメラ名の入力方法を合わせる
		var target_form = info_form_elements.item(i);
		var target_form_select = target_form.camera_form_select;
		target_form_select.value = form_select;

		//カメラ名の入力方法で分岐
		if(form_select == "existing_camera"){
			//既存のカメラ名から選択していたら値を合わせる
			target_form.camera_name_select.value = camera_value;

			//クラスを変更
			target_form.camera_name_select.className = "camera_name_select";
			target_form.camera_name_input.className = "passive";

		}else if(form_select == "new_camera"){
			//新しいカメラ名を入力していたら入力したカメラ名を合わせる
			target_form.camera_name_input.value = camera_value;

			//クラスを変更
			target_form.camera_name_select.className = "passive";
			target_form.camera_name_input.className = "camera_name_input";
		}
	}
	//メッセージを表示
	alert("カメラ名を反映しました。");
}

//ファイルアップロードで使うWorkerオブジェクトを作成
var upload_worker = new Worker('file_upload.js');

function step3_submit(){
//確認のダイアログを出す
if(window.confirm('設定した情報で写真をアップロードします。\n問題がなければOKを押してください。')){
	//OKをクリックしたら以降の処理を実行
	//インジケータを表示
	var indicator_element = document.getElementById("indicator");
	indicator_element.style.display = 'block';
	//0.5秒後にアップロードを実行する関数を呼ぶ
	setTimeout("file_upload()",500);
}
}

function file_upload(){
	//保存したファイル名を格納する配列を宣言する(重複していた場合、変更したファイル名をDBに登録する為)
	var filename_results = [];

	//インジケータの要素を変数に入れておく
	var indicator_element = document.getElementById("indicator");
	//通信に失敗したりphpからメッセージがあったら立てるフラグ
	var error_flug = "off";

	//ファイルを選択したinput要素を変数に格納
	var input_element = document.getElementById("upfile");

	//ファイル数を変数に格納
	var file_count = input_element.files.length;

	//処理した日付を宣言しておく ※途中で日を跨いだ時の対策
	var tmp_date = new Date();
	var year = tmp_date.getFullYear();
	var month = ("00" + (tmp_date.getMonth()+1)).slice(-2);
	var day = ("00" + tmp_date.getDate()).slice(-2);
	var process_date = "" + year + month + day;

	//step.1で選択した日付を変数に格納
	var step1_from = document.upload_date_form;
	var filming_century = step1_from.upload_century_select.value;
	var filming_year = step1_from.upload_year_select.value;
	var filming_month = step1_from.upload_month_select.value;
	var filming_day = step1_from.upload_day_select.value;
	var filming_date = filming_century + filming_year + "/" + filming_month + "/" + filming_day;

	//ファイル選択フォームを有効化する※falseだと選択したファイルの情報をphpに渡すことができない
	var step2_from = document.file_select_form;
	var step2_upfile = step2_from.getElementsByTagName('input');
	step2_upfile.item(0).disabled = false;

	//ファイルデータを取得
	var formdata = new FormData($('#file_select_form').get(0));

	//ファイルデータをまとめてphpに送る
	$.ajax({
		url  : "file_upload_process.php",
		type : "POST",
		data : formdata,
		contentType : false,
		processData : false,
		timeout:60000,
		async: false
	})
	//.done(function(data, textStatus, jqXHR){
	.done(function(data, textStatus, jqXHR){
		//戻り値をファイル名の配列に格納
		if(file_count >= 2){
			//ファイル数が2つ以上の場合は配列に入れる
			var filename_results = data.split(',');
		}else{
			//ファイル数が1つの場合は普通の変数に入れる
			var filename_result = data;
		}

		//ここからフォームに入力した情報をDBに登録する処理
		//まずフォーム要素の集まりを取得
		var info_field_div = document.getElementsByClassName("info_field").item(0);
		var info_form_elements = info_field_div.getElementsByTagName('form');

		//ファイルの数だけ繰り返し
		for(var i = 0 ; i < file_count ; i++){
			//フォームから入力した値を取得する
			//・タイトル
			var title = info_form_elements.item(i).title_input.value;
			//・番号
			var film_number = info_form_elements.item(i).num_input.value;
			//・お気に入り度
			var radio_elements = info_form_elements.item(i).stars_select;
			if(radio_elements[0].checked){var favorites_level = 1;}
			if(radio_elements[1].checked){var favorites_level = 2;}
			if(radio_elements[2].checked){var favorites_level = 3;}
			if(radio_elements[3].checked){var favorites_level = 4;}
			if(radio_elements[4].checked){var favorites_level = 5;}
			//・撮影カメラの選択方法
			var form_select = info_form_elements.item(i).camera_form_select.value;
			if(form_select == "existing_camera"){
				//「既存のカメラ名から選択」が選ばれていたらプルダウンメニューから値を取得
				var camera_name_or_code = info_form_elements.item(i).camera_name_select.value;
			}else if(form_select == "new_camera"){
				//「新しいカメラ名を入力」が選ばれていたらテキストボックスから値を取得
				var camera_name_or_code = info_form_elements.item(i).camera_name_input.value;
			}
			//・非表示フラグ
			var secret_flug = info_form_elements.item(i).secret_flug_select.value;
			//・メモ
			var memo = info_form_elements.item(i).memo_input.value;

			//保存したファイル名
			if(file_count >= 2){
				//ファイル数が2つ以上の場合は配列からファイル名を取得
				var file_name = filename_results[i];
			}else{
				//ファイル数が1つの場合は普通の変数からファイル名を取得
				var file_name = filename_result;
			}

			//phpに処理を渡す
			php_func(filming_date,film_number,title,favorites_level,form_select,camera_name_or_code,secret_flug,memo,file_name,process_date,file_count).done(function(kekka){
				//処理が完了したら以下の処理
				//phpで処理中に何かエラーメッセージを出力したら表示
				if(!(kekka == "")){
					alert("phpからのメッセージ:" + kekka);
					var error_flug = "on";
				}
			}).fail(function(jqXHR, textStatus, errorThrown){
				//DBにinsert前、通信に失敗したらアラートを出して処理終了
				alert("通信に失敗しました。(DBにinsert前)" + jqXHR.status + '\n' + textStatus + '\n' + errorThrown);
				var error_flug = "on";
				//インジケータを非表示
				indicator_element.style.display = 'none';
			});
		}
		//ファイルの数だけ繰り返しの処理が終わったら以下の処理
		//ファイル選択フォームを無効化する
		step2_upfile.item(0).disabled = true;
		alert("処理が終了しました。");
		//インジケータを非表示
		indicator_element.style.display = 'none';
		//エラーメッセージ無く完了した場合はリロードする
		if(error_flug == "off"){
			//ページをリロードする
			location.reload();
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		//ファイルアップロード時、通信に失敗したらアラートを出して処理終了
		alert('通信に失敗しました。(ファイルアップロード時)\n' + jqXHR.status + '\n' + textStatus + '\n' + errorThrown);
		//インジケータを非表示
		indicator_element.style.display = 'none';
	});
//インジケータを非表示
indicator_element.style.display = 'none';
}

//phpに処理を渡してinsert文を実行する関数
function php_func(filming_date,film_number,title,favorites_level,form_select,camera_name_or_code,secret_flug,memo,file_name,process_date,file_count){
return $.ajax({
	type: 'POST',
	url: 'db_insert.php',
	timeout:60000,
	async: false,
	data: {
	       'filming_date' : filming_date,
	       'film_number' : film_number,
	       'title' : title,
	       'favorites_level' : favorites_level,
	       'form_select' : form_select,
	       'camera_name_or_code' : camera_name_or_code,
	       'secret_flug' : secret_flug,
	       'memo' : memo,
	       'file_name' : file_name,
	       'process_date' : process_date,
	       'file_count' : file_count
	      }
})
}