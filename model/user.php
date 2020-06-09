<?php
require_once($install_root . '/config.php');
require_once($install_root . '/utils/podcastParser.class.php');
require_once($install_root . '/model/podcast.php');

class User {

	public $id, $username, $email, $bio, $website, $twitter, $facebook, $password, $salt, $subscribedIds;

	function __construct($user) {
		global $adodb, $long_cache, $short_cache;
		$this->subscribedId = array();
		$query = 'SELECT * FROM users WHERE lower(username) = lower(' . $adodb->qstr($user) . ') LIMIT 1';
		$adodb->SetFetchMode(ADODB_FETCH_ASSOC);
		$row = $adodb->CacheGetRow($long_cache, $query);
		if (!$row) {
			throw new Exception('User not found');
		}
		if (is_array($row)) {
			$this->id		= $row['id'];
			$this->username		= $row['username'];
			$this->password		= $row['password'];
			$this->email		= $row['email'];
			$this->bio		= $row['bio'];
			$this->website		= $row['website'];
			$this->twitter		= $row['twitter'];
			$this->salt		= $row['salt'];

			$res = $adodb->CacheGetAll($short_cache, 'SELECT podcast FROM subscription JOIN podcast ON subscription.podcast = podcast.id WHERE user=' . $this->id . ' ORDER BY podcast.name ASC');
			foreach ($res as &$row) {
				$this->subscribedIds[] = $row['podcast'];
			}
		}
	}

	function save() {
		global $adodb;
		$adodb->Execute('UPDATE users SET email=?, bio=?, website=?, twitter=?, facebook=?, password=?',
			array($this->email, $this->bio, $this->website, $this->twitter, $this->facebook, $this->password));

		$adodb->CacheFlush('SELECT * FROM users WHERE lower(username) = lower(' . $adodb->qstr($this->username) . ') LIMIT 1');
	}

	function addCommand($command, $uuid) {
		global $adodb;
		$res = $adodb->GetAll('SELECT uuid FROM device_token WHERE user = ? AND uuid != ?', array($this->id, $uuid));
		foreach($res as &$row) {
			$adodb->Execute('INSERT INTO commands (user, device, command) VALUES(?, ?, ?)', array($this->id, $row['uuid'], $command));
		}
	}

	function getCommands($uuid) {
		global $adodb;
		$commands = array();
		$res = $adodb->GetAll('SELECT command FROM commands WHERE user = ? AND device = ?', array($this->id, $uuid));
		$adodb->Execute('DELETE FROM commands WHERE user = ? AND device = ?', array($this->id, $uuid));
		foreach($res as &$row) {
			$commands[] = $row['command'];
		}
		return $commands;
	}

	function subscribe($feed, $uuid = '') {
		global $adodb;
		$podcast = Podcast::from_feed($feed);
		if($podcast !== false) {
			$subscribed = $adodb->GetOne('SELECT podcast FROM subscription WHERE user=' . $this->id . ' AND podcast=' . $podcast->id);
			if(!$subscribed) {
				$adodb->Execute('INSERT INTO subscription(user, podcast) VALUES(?, ?)', array($this->id, $podcast->id));
				$adodb->CacheFlush('SELECT podcast FROM subscription JOIN podcast ON subscription.podcast = podcast.id WHERE user=' . $this->id . ' ORDER BY podcast.name ASC');
			} else {
				$adodb->Execute('UPDATE subscription SET lastupdate = NOW() WHERE user=? AND podcast=?', array($this->id, $podcast->id));
			}
			$this->addCommand("subscribe $feed", $uuid);
		}
	}

        function unsubscribe($feed, $uuid = '') {
		global $adodb;
		$podcast = Podcast::from_feed($feed);
		if($podcast !== false) {
			$adodb->Execute('DELETE FROM subscription WHERE user=? AND podcast=?', array($this->id, $podcast->id));
			$adodb->CacheFlush('SELECT podcast FROM subscription JOIN podcast ON subscription.podcast = podcast.id WHERE user=' . $this->id . ' ORDER BY podcast.name ASC');
			$this->addCommand("unsubscribe $feed", $uuid);
		}
	}

	function subscriptions() {
		global $adodb, $short_cache;
		$podcasts = array();
		$adodb->SetFetchMode(ADODB_FETCH_ASSOC);
		$res = $adodb->CacheGetAll($short_cache, 'SELECT podcast FROM subscription JOIN podcast ON subscription.podcast = podcast.id WHERE user=' . $this->id . ' ORDER BY podcast.name ASC');
		foreach ($res as &$row) {
			$podcasts[] = new Podcast($row['podcast']);
		}
		return $podcasts;
	}

	function setToken($token, $uuid) {
		global $adodb;
		$adodb->Execute('REPLACE INTO device_token VALUES (?, ?, ?)', array($this->id, $uuid, $token));
	}

	function getRecommended($limit = 10) {
		global $adodb, $long_cache;

		// Find podcasts from the 10 users with the most similar tastes
		// that this user isn't already subscribed to
		$res = $adodb->CacheGetAll($long_cache, 'SELECT distinct(podcast) FROM subscription WHERE user IN (SELECT user FROM (SELECT subscription.user AS user, count(subscription.user) AS shared_podcasts FROM subscription INNER JOIN (SELECT DISTINCT(podcast) AS podcast FROM subscription WHERE user=?) AS subscribed_podcasts ON subscription.podcast = subscribed_podcasts.podcast WHERE user != ? GROUP BY subscription.user ORDER BY shared_podcasts DESC LIMIT 10) as neighbours) AND podcast NOT IN (SELECT podcast FROM subscription WHERE user=?)', array($this->id, $this->id, $this->id));
		
		// Return a random selection of podcasts that they subscribe to
		shuffle($res);
		$res = array_slice($res, 0, $limit);
		$podcasts = array();
		foreach ($res as &$row) {
			$podcasts[] = new Podcast($row['podcast']);
		}
		return $podcasts;
	}

}
