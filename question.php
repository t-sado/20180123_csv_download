<?php
	//URL���w�肷��
	$url = 'https://premier.no1s.biz/';
	//�F�؃f�[�^
	$data = array(
	    'email' => 'micky.mouse@no1s.biz',
	    'password' => 'micky',
	);
	//�e���|�����t�@�C�����쐬����
	$cookie = tempnam(sys_get_temp_dir(),'cookie_');
	//cURL�����������Ďg�p�\�ɂ���

	$curl = curl_init();
	// �I�v�V�����ݒ�
	$options = array(
		CURLOPT_URL            => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIEJAR      => $cookie,		
	);
	curl_setopt_array($curl, $options);
	//URL�ɃA�N�Z�X���A���ʂ𕶎���Ƃ��ĕԂ�
	$html = curl_exec($curl);
	//cURL�̃��\�[�X���������
	curl_close($curl);

	//Document������
	$dom = new DOMDocument();
	//html�������ǂݍ��ށihtml�Ɍ�肪����ꍇ�G���[���o��̂�@������j
	@$dom->loadHTML($html);
	//XPath������
	$xpath = new DOMXPath($dom);
	//input��type��hidden�̗v�f���Ƃ��Ă���
	$node = $xpath->query('//input[@type="hidden"]');
	foreach($node as $v)
	{
		//POST�f�[�^�ɒǉ�
		$data[$v->getAttribute('name')] = $v->getAttribute('value');
	}

	//cURL�����������Ďg�p�\�ɂ���
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
	//URL�ɃA�N�Z�X���A���ʂ��擾
	$result = curl_exec($curl);

	// 2�y�[�W��
	curl_setopt($curl, CURLOPT_URL, 'https://premier.no1s.biz/admin?page=2');
	$result .= curl_exec($curl);
	
	// 3�y�[�W��
	curl_setopt($curl, CURLOPT_URL, 'https://premier.no1s.biz/admin?page=3');
	$result .= curl_exec($curl);

	//cURL�̃��\�[�X���������
	curl_close($curl);
	//�e���|�����t�@�C�����폜
	unlink($cookie);

	$result = str_replace(array("\r\n","\r","\n"), '', $result);
	$result = explode('<table class="table table-striped" cellpadding="0" cellspacing="0">', $result);

	// CSV�ɏ�������
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

	// �_�u���N�H�[�e�[�V����������
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
