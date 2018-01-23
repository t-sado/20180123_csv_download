<?php
	//URLを指定する
	$url = 'https://premier.no1s.biz/';
	//認証データ
	$data = array(
	    'email' => 'micky.mouse@no1s.biz',
	    'password' => 'micky',
	);
	//テンポラリファイルを作成する
	$cookie = tempnam(sys_get_temp_dir(),'cookie_');
	//cURLを初期化して使用可能にする

	$curl = curl_init();
	// オプション設定
	$options = array(
		CURLOPT_URL            => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIEJAR      => $cookie,		
	);
	curl_setopt_array($curl, $options);
	//URLにアクセスし、結果を文字列として返す
	$html = curl_exec($curl);
	//cURLのリソースを解放する
	curl_close($curl);

	//Document初期化
	$dom = new DOMDocument();
	//html文字列を読み込む（htmlに誤りがある場合エラーが出るので@をつける）
	@$dom->loadHTML($html);
	//XPath初期化
	$xpath = new DOMXPath($dom);
	//inputのtypeがhiddenの要素をとってくる
	$node = $xpath->query('//input[@type="hidden"]');
	foreach($node as $v)
	{
		//POSTデータに追加
		$data[$v->getAttribute('name')] = $v->getAttribute('value');
	}

	//cURLを初期化して使用可能にする
	$curl = curl_init();
	$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_URL => $url,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_COOKIEFILE => $cookie,
		CURLOPT_FOLLOWLOCATION => true
	);
	curl_setopt_array($curl, $options);
	//URLにアクセスし、結果を取得
	$result = curl_exec($curl);

	// 2ページ目
	curl_setopt($curl, CURLOPT_URL, 'https://premier.no1s.biz/admin?page=2');
	$result .= curl_exec($curl);
	
	// 3ページ目
	curl_setopt($curl, CURLOPT_URL, 'https://premier.no1s.biz/admin?page=3');
	$result .= curl_exec($curl);

	//cURLのリソースを解放する
	curl_close($curl);
	//テンポラリファイルを削除
	unlink($cookie);

	$result = str_replace(array("\r\n","\r","\n"), '', $result);
	$result = explode('<table class="table table-striped" cellpadding="0" cellspacing="0">', $result);

	// CSVに書き込み
	$file = fopen("download.csv","w");
	foreach ($result as $key => $value) {
		if (!$key) {
			continue;
		}
		$tr = explode('<tr>', $value);
		foreach ($tr as $td) {
			preg_match_all("/<td>(.+?)<\/td>/", $td, $match);
			if ($match[1]) {
				mb_fputcsv($file, $match[1]);
			}
		}
	}
	fclose($file);

	// ダブルクォーテーションをつける
	function mb_fputcsv($fp=null, $fields=null, $delimiter=',', $enclosure='"', $rfc=false) {

		$str = null;
		$cnt = 0;
		$last = count($fields);

		foreach($fields as $val) {
			$cnt++;
			$val = (!$rfc) ? preg_replace('/(?<!\\\\\\\\)\"/u', '""', $val) : preg_replace('/\"/u', '""', $val);
			$str.= '"'. $val. '"';
			if($cnt != $last) {
				$str.= ',';
			}
		}
		fwrite($fp, $str . "\r\n");
	}
