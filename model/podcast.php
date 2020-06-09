<?php
require_once($install_root . '/config.php');
require_once($install_root . '/utils/podcastParser.class.php');

class Podcast {

	public $id, $artist, $name, $website, $description, $feed, $image, $lastupdate;

	function __construct($id) {
		global $adodb, $long_cache;
		$query = 'SELECT * FROM podcast WHERE id = ' . (int) $id . ' LIMIT 1';
		$adodb->SetFetchMode(ADODB_FETCH_ASSOC);
		$row = $adodb->CacheGetRow($long_cache, $query);
		if (!$row) {
			throw new Exception('Podcast not found');
		}
		if (is_array($row)) {
			$this->id		= $row['id'];
			$this->artist		= $row['artist'];
			$this->name		= $row['name'];
			$this->website		= $row['website'];
			$this->description	= $row['description'];
			$this->feed		= $row['feed'];
			$this->image		= $row['image'];
			$this->lastupdate	= $row['lastupdate'];
		}
	}
	/**
	 * Create Podcast object from feed URL
	 *
	 * @param string $feed Feed URL
	 * @return Podcast Podcast object, or false in case of failure
	 */

	public static function from_feed($feed) {
		global $adodb;
		$id = $adodb->GetOne('SELECT id FROM podcast WHERE feed = ' . $adodb->qstr($feed));

		if ($id) {
			return new Podcast($id);
		} else {
			$parser = new PodcastParser($feed);
			$title = $parser->getPodcastTitle();
			$author = $parser->getPodcastAuthor();
			$description = $parser->getPodcastDescription();
			$website = $parser->getPodcastWebsite();
			$image = $parser->getPodcastFeedImageURL();
			$adodb->Execute('INSERT INTO podcast(artist, name, description, website, image, feed) VALUES(?, ?, ?, ?, ? ,?)',
				array($author, $title, $description, $website, $image, $feed));
			return Podcast::from_feed($feed);
		}
	}


	function save() {
		global $adodb;
		$adodb->Execute('UPDATE podcast SET artist=?, name=?, description=?, feed=?, image=?, lastupdate=?',
			array($this->artist, $this->name, $this->description, $this->feed, $this->image, $this->lastupdate));

		$adodb->CacheFlush('SELECT * FROM podcast WHERE id = ' . (int) $this->id . ' LIMIT 1');
	}

	function updateEpisodes() {
		global $adodb;
		$parser = new PodcastParser($this->feed);

	}

	function episodes() {
		global $adodb, $long_cache;
		$adodb->SetFetchMode(ADODB_FETCH_ASSOC);
		$episodes = array();
		$result = $adodb->CacheGetAll($long_cache, "SELECT * FROM episode WHERE podcast = " . (int) $this->id . ' ORDER BY published DESC');
		foreach($result as &$row) {
			$episodes[] = $row;
		}
		return $episodes;
	}

	function subscribed($user) {
		return in_array($this->id, $user->subscribedIds);
	}

}
