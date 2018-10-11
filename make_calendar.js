window.onload = function() {
    // 現在の年月の取得
    var current = new Date();
    var year = current.getFullYear();
    var month = current.getMonth() + 1;
 
    // カレンダーの表示
    var wrapper = document.getElementById('calendar');
    add_calendar(wrapper, year, month);
}
 
/**
 * 指定した年月のカレンダーを表示する
 * @param {object} wrapper - カレンダーを追加する親要素
 * @param {number} year    - 年の指定
 * @param {number} month   - 月の指定
 */
function add_calendar(wrapper, year, month) {
    // 現在カレンダーが追加されている場合は一旦削除する
    wrapper.textContent = null;
 
    // カレンダーに表示する内容を取得
    var headData = generate_calendar_header(wrapper, year, month);
    var bodyData = generate_month_calendar(year, month);
 
    // カレンダーの要素を追加
    wrapper.appendChild(headData);
    wrapper.appendChild(bodyData);
}
 
/**
 * 指定した年月のカレンダーのヘッダー要素を生成して返す
 * @param {object} wrapper - カレンダーを追加する親要素
 * @param {number} year    - 年の指定
 * @param {number} month   - 月の指定
 */
function generate_calendar_header(wrapper, year, month) {
    // 前年と翌年、前月と翌月を取得
    var nextYear = new Date(year, (month - 1));
    nextYear.setFullYear(nextYear.getFullYear() + 1);
    var prevYear = new Date(year, (month - 1));
    prevYear.setFullYear(prevYear.getFullYear() - 1);
    var nextMonth = new Date(year, (month - 1));
    nextMonth.setMonth(nextMonth.getMonth() + 1);
    var prevMonth = new Date(year, (month - 1));
    prevMonth.setMonth(prevMonth.getMonth() - 1);

    // ヘッダー要素
    var cHeader = document.createElement('div');
    cHeader.className = 'calendar-header';

    // 前年ボタンの追加
    var yPrev = document.createElement('button');
    yPrev.className = 'calendar-header_button';
    var yPrevText = document.createTextNode('<<');
    yPrev.appendChild(yPrevText);
    // 前年ボタンをクリックした時のイベント設定
    yPrev.addEventListener('click', function() {
        add_calendar(wrapper, prevYear.getFullYear(), (prevYear.getMonth() + 1));
    }, false);
    cHeader.appendChild(yPrev);

    // 前月ボタンの追加
    var cPrev = document.createElement('button');
    cPrev.className = 'calendar-header_button';
    var cPrevText = document.createTextNode('<');
    cPrev.appendChild(cPrevText);
    // 前月ボタンをクリックした時のイベント設定
    cPrev.addEventListener('click', function() {
        add_calendar(wrapper, prevMonth.getFullYear(), (prevMonth.getMonth() + 1));
    }, false);
    cHeader.appendChild(cPrev);

    // 見出しの追加
    var cTitle = document.createElement('div');
    cTitle.className = 'calendar-header__title';
    var cTitleText = document.createTextNode(year + '年' + month + '月');
    cTitle.appendChild(cTitleText);
    cHeader.appendChild(cTitle);

    // 翌月ボタンの追加
    var cNext = document.createElement('button');
    cNext.className = 'calendar-header_button';
    var cNextText = document.createTextNode('>');
    cNext.appendChild(cNextText);
    // 翌月ボタンをクリックした時のイベント設定
    cNext.addEventListener('click', function() {
        add_calendar(wrapper, nextMonth.getFullYear(), (nextMonth.getMonth() + 1));
    }, false);
    cHeader.appendChild(cNext);

    // 翌年ボタンの追加
    var yNext = document.createElement('button');
    yNext.className = 'calendar-header_button';
    var yNextText = document.createTextNode('>>');
    yNext.appendChild(yNextText);
    // 翌月ボタンをクリックした時のイベント設定
    yNext.addEventListener('click', function() {
        add_calendar(wrapper, nextYear.getFullYear(), (nextYear.getMonth() + 1));
    }, false);
    cHeader.appendChild(yNext);

    return cHeader;
}
 
/**
 * 指定した年月のカレンダー要素を生成して返す
 * @param {number} year  - 年の指定
 * @param {number} month - 月の指定
 */
function generate_month_calendar(year, month) {
    var weekdayData = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    // カレンダーの情報を取得
    var calendarData = get_month_calendar(year, month);
 
    var i = calendarData[0]['weekday']; // 初日の曜日を取得
    // カレンダー上の初日より前を埋める
    while(i > 0) {
        i--;
        calendarData.unshift({
            day: '',
            weekday: i
        });
    }
    var i = calendarData[calendarData.length - 1]['weekday']; // 末日の曜日を取得
    // カレンダー上の末日より後を埋める
    while(i < 6) {
        i++;
        calendarData.push({
            day: '',
            weekday: i
        });
    }
 
    // カレンダーの要素を生成
    var cTable = document.createElement('table');
    cTable.className = 'calendar-table';
 
    var insertData = '';
    // 曜日部分の生成
    insertData += '<thead>';
    insertData += '<tr>';
    for (var i = 0; i < weekdayData.length; i++) {
	if(weekdayData[i] == "Sun"){
		insertData += '<th><font color="#da2514">';
		insertData += weekdayData[i];
		insertData += '</font>';
	}else if(weekdayData[i] == "Sat"){
		insertData += '<th><font color="#0175cb">';
		insertData += weekdayData[i];
		insertData += '</font>';
	}else{
	        insertData += '<th>';
	        insertData += weekdayData[i];
	}
        insertData += '</th>';
    }
    insertData += '</tr>';
    insertData += '</thead>';
 
    // 日付部分の生成(ここにIFを追記する)
    insertData += '<tbody>';
    for (var i = 0; i < calendarData.length; i++) {
        if(calendarData[i]['weekday'] <= 0) {
            insertData += '<tr>';
        }
        insertData += '<td>';
	//日付を書く
	var php_day = calendarData[i]['day'];
	php_func(year, month, php_day).done(function(php_back){
		if(php_back > 0){
	        	insertData += '<form class="calendar-link" method="GET" action="search_result_page.php">';
			insertData += '<input type="submit" value="' + php_day + '">';
			insertData += '<input type="hidden" name="filming_date" value="' + year + '/' + month + '/' + php_day + '">';
			insertData += '<input type="hidden" name="via_page" value="calendar">';
			insertData += '</form>';
		}else{
		        insertData += calendarData[i]['day'];
		}
	}).fail(function(php_back){
		insertData += '×';
	});

        insertData += '</td>';
        if(calendarData[i]['weekday'] >= 6) {
            insertData += '</tr>';
        }
    }
    insertData += '</tbody>';
 
    cTable.innerHTML = insertData;
    return cTable;
}
 
/**
 * 指定した年月のカレンダー情報を返す
 * @param {number} year  - 年の指定
 * @param {number} month - 月の指定
 */
function get_month_calendar(year, month) {
    var firstDate = new Date(year, (month - 1), 1); // 指定した年月の初日の情報
    var lastDay = new Date(year, (firstDate.getMonth() + 1), 0).getDate(); // 指定した年月の末日
    var weekday = firstDate.getDay(); // 指定した年月の初日の曜日
 
    var calendarData = []; // カレンダーの情報を格納
    var weekdayCount = weekday; // 曜日のカウント用
    for (var i = 0; i < lastDay; i++) {
        calendarData[i] = {
            day: i + 1,
            weekday: weekdayCount
        }
        // 曜日のカウントが6(土曜日)まできたら0(日曜日)に戻す
        if(weekdayCount >= 6) {
            weekdayCount = 0;
        } else {
            weekdayCount++;
        }
    }
    return calendarData;
}

function php_func(year, month, php_day){
return $.ajax({
    type: 'POST',
    url: 'make_calendar_link.php',
    async: false,
    data: {
	  'name1' : year + '/' + month + '/' + php_day
	  }
})
}
