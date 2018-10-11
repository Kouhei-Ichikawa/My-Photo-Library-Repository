// ファイルを読み込む
onmessage = function (event) {
	//選択されたファイルの情報を同期しながら取得
	var reader = new FileReaderSync();

	//ファイルのデータをURLにして変数に格納
	var src = "";
	src = reader.readAsDataURL(event.data.file);

	//呼び出し元のjavascriptにファイル名(name)、ファイルデータURL(src)を返す
	postMessage({"name": event.data.file.name, "src": src});
};