<?php
require_once($install_root . '/config.php');
require_once($install_root . '/model/podcast.php');

class Chart {

	function top($limit=10) {
		global $adodb, $long_cache;
		$podcasts = array();
		$adodb->SetFetchMode(ADODB_FETCH_ASSOC);
		$res = $adodb->CacheGetAll($long_cache, 'SELECT podcast, count(podcast) as subscribers FROM subscription GROUP BY podcast ORDER BY subscribers DESC');
		foreach ($res as &$row) {
			$chart[] = array(new Podcast($row['podcast']), $row['subscribers']);
		}
		return $chart;
	}

}
