//無効な日付を回避するスクリプト
function last_day_func(form_type){

//呼び出したフォームによってオブジェクトの指定を変える
if(form_type == "from"){
	//呼び出し元が詳細検索の開始日だった場合
	var century_element = document.detailed_form.from_century;
	var year_element = document.detailed_form.from_year;
	var month_element = document.detailed_form.from_month;
	var day_element = document.detailed_form.from_day;

}else if(form_type == "till"){
	//呼び出し元が詳細検索の終了日だった場合
	var century_element = document.detailed_form.till_century;
	var year_element = document.detailed_form.till_year;
	var month_element = document.detailed_form.till_month;
	var day_element = document.detailed_form.till_day;

}else if(form_type == "edit"){
	//呼び出し元が登録情報編集ページのフォームだった場合
	var century_element = document.editing_form.filming_century_select;
	var year_element = document.editing_form.filming_year_select;
	var month_element = document.editing_form.filming_month_select;
	var day_element = document.editing_form.filming_day_select;

}else if(form_type == "upload"){
	//呼び出し元がアップロードページのフォームだった場合
	var century_element = document.upload_date_form.upload_century_select;
	var year_element = document.upload_date_form.upload_year_select;
	var month_element = document.upload_date_form.upload_month_select;
	var day_element = document.upload_date_form.upload_day_select;
}

//選択した世紀、年代、月を取り出す
var filming_century = century_element.value;
var filming_year = year_element.value;
//世紀、年代を文字列として結合した後、数値型にして4桁の年表示を取得している
var filming_full_year = Number("" + filming_century + filming_year);
var filming_month = month_element.value;

//29～31日のプルダウンの表示をリセット
//filming_day_selectのoptionの要素数を数える
var options_count = day_element.childElementCount;

//以前のスクリプトの実行で数が減っていた場合は補充する
for(var i = 31 - options_count ; i > 0 ; i--){
	//selectのlengthプロパティの値を１増やす。
	day_element.length++;
	//option要素の数を変数に格納
	var new_option = day_element.length;
	//新しいoption要素の値を設定
	day_element.options[new_option - 1].value = new_option;
	day_element.options[new_option - 1].text = new_option;
}

//月ごとに分岐してlast_dayを出す
if(filming_month == "01"){
	var last_day = 31;
}else if(filming_month == "02"){
	//2月の場合はうるう年か判定
	if((filming_full_year % 4 === 0 && filming_full_year % 100 !== 0) || filming_full_year % 400 === 0){
		//うるう年だったら29日が最後
		var last_day = 29;
	}else{
		//違かったら28日が最後
		var last_day = 28;
	}
}else if(filming_month == "03"){
	var last_day = 31;
}else if(filming_month == "04"){
	var last_day = 30;
}else if(filming_month == "05"){
	var last_day = 31;
}else if(filming_month == "06"){
	var last_day = 30;
}else if(filming_month == "07"){
	var last_day = 31;
}else if(filming_month == "08"){
	var last_day = 31;
}else if(filming_month == "09"){
	var last_day = 30;
}else if(filming_month == "10"){
	var last_day = 31;
}else if(filming_month == "11"){
	var last_day = 30;
}else if(filming_month == "12"){
	var last_day = 31;
}

//last_day + 1 ～ 31日までのoption要素を消す
for(var i = last_day ; i < 31 ; i++){
	//option要素は配列番号で指定。last_dayを配列番号にするとちょうど無効な日付を指定できる
	day_element.options[last_day] = null;
}

//終わり
}
