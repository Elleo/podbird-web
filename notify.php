<?php

require_once('database.php');
require_once($install_root . '/simplepie/autoloader.php');

$adodb->SetFetchMode(ADODB_FETCH_ASSOC);
$podcasts = $adodb->GetAll('SELECT * FROM podcast');

$total = array();

foreach($podcasts as $podcast) {

	$feed = new SimplePie();
	$feed->set_cache_location($install_root . '/simplepie-cache/');
	$feed->set_feed_url($podcast['feed']);
	$feed->enable_order_by_date(true);
	$feed->init();

	$subscriptions = $adodb->GetAll('SELECT user, podcast, UNIX_TIMESTAMP(lastupdate) as lastupdate FROM subscription WHERE podcast = ?', $podcast['id']);
	
	foreach ($feed->get_items() as $item) {
		$guid = $item->get_id();
		$res = $adodb->GetOne('SELECT * FROM episode WHERE guid = ?', $guid);
		if ($res != null) {
			// We've already recorded this episode and any older ones
			break;
		}

		$enclosure = $item->get_enclosure();

		if($enclosure != null) {

			$adodb->Execute('INSERT INTO episode (guid, podcast, name, description, duration, audiourl, published)
					VALUES (?, ?, ?, ?, ?, ?, FROM_UNIXTIME(?))', array( 
					$guid,
					$podcast['id'],
					$item->get_title(),
					$item->get_description(),
					$enclosure->get_duration(),
					$enclosure->get_link(),
					$item->get_date('U')));

			foreach($subscriptions as $sub) {
				if ($sub['lastupdate'] < intval($item->get_date('U'))) {
					$rows = $adodb->CacheGetAll(600, 'SELECT token FROM device_token WHERE user = ?', $sub['user']);
					if (!array_key_exists($sub['user'], $total)) {
						$total[$sub['user']] = 1;
					} else {
						$total[$sub['user']]++;
					}
					foreach($rows as &$row) {
						notify($row['token'], $feed->get_title(), $item->get_title(), $podcast['feed'], $feed->get_image_url(), $total[$sub['user']]);
					}
				}
			}
		}

	}

	$adodb->Execute('UPDATE subscription SET lastupdate = CURRENT_TIMESTAMP WHERE podcast = ?', $podcast['id']);
}


function notify($token, $podcast, $episode, $feed, $icon = '', $totalNew=1) {
	if ($icon == null) {
		$icon = '';
	}
	$appIds = array('com.mikeasoft.podbird', 'podbird.nik90');
	foreach($appIds as $appId) {
		$message = '{   
			"appid": "' . $appId .'_Podbird",
			"expire_on": "' . strftime("%FT%T.000Z", time() + 86400) . '",
			"token": "' . $token . '",
			"clear_pending": true, 
			"replace_tag": "' . $feed .'",
			"data": {
				"message": "' . $podcast . '",
				"notification": {
					"tag": "' . $feed . '",
					"card": {
						"summary": "' . $podcast . '",
						"body": "' . $episode . '",
						"popup": true,
						"persist": true,
						"timestamp": ' . time() . ',
						"icon": "' . $icon . '",
						"actions": ["appid://' . $appId . '/Podbird/current-user-version"]
					},
					"emblem-counter": {
						"count": ' . $totalNew . ',
						"visible": true
					}
				}
			}
		}';

		$header = "
POST /notify HTTP/1.1 
Host: push.ubuntu.com
Content-type: application/json 
Content-length: ".strlen($message)."
Connection: close\r\n\r\n"; 

		// open the connection 
		$f = fsockopen("tls://push.ubuntu.com", 443);

		fputs($f, $header . $message); 

		$result = '';

		// get the response 
		while (!feof($f)) $result .= fread($f,32000); 

		fclose($f);
	}
}
