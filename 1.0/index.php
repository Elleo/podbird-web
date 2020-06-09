<?php

/* Podbird

   Copyright (C) 2015 Michael Sheldon

   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

require_once('../database.php');
require_once('../model/user.php');
require_once('../model/chart.php');

# Resolves method= parameters to handler functions
$method_map = @array(
	'chart.getfeatured'  		=> method_chart_getFeatured,
	'chart.gettoppodcasts'   	=> method_chart_getTopPodcasts,
	'episode.getposition'		=> method_episode_getPosition,
	'episode.setposition'		=> method_episode_setPosition,
	'podcast.subscribe'     	=> method_podcast_subscribe,
	'podcast.unsubscribe'     	=> method_podcast_unsubscribe,
	'user.getcommands'		=> method_user_getCommands,
	'user.getrecommendations'	=> method_user_getRecommendations,
	'user.getsubscriptions'		=> method_user_getSubscriptions,
	'user.settoken'			=> method_user_setToken,
);

function method_chart_getFeatured() { }

function method_chart_getTopPodcasts() {
	$chart = new Chart();
	$chartEntries = $chart->top();

	$xml = new SimpleXMLElement('<podbird status="ok"></podbird>');

	$root = $xml->addChild('chart', null);
	$position = 1;
	foreach ($chartEntries as $entry) {
		$podcast_node = add_podcast_node($root, $entry[0]);
		$podcast_node->addAttribute('position', $position);
		$podcast_node->addAttribute('listeners', $entry[1]);
		$position++;
	}
	
	respond($xml);
}

function method_episode_getPosition() { }

function method_episode_setPosition() { }

/**
 * podcast.subscribe : Subscribe a user to a podcast
 *
 * ###Description
 * Create a record for a podcast in the database if it doesn't already exist and subscribe this user to it.
 *
 * ###Parameters
 * * **user** (required)		: User to subscribe to the podcast.
 * * **password** (required)		: md5sum of user's password.
 * * **feed** (required)		: Podcast's feed URL.
 * * **uuid** (required)		: This device's unique identifier.
 * * **format** (optional)		: Format of response, **xml** or **json**. Default is xml.
 * - - -
 *
 * @package Webservice
 * @subpackage Podcast
 * @api
 */
function method_podcast_subscribe() {
	if (!isset($_REQUEST['feed'])) {
		die("Feed parameter isn't set");
	}
	if (!isset($_REQUEST['uuid'])) {
		die("UUID parameter isn't set");
	}

	$user = get_user();
	$user->subscribe($_REQUEST['feed'], $_REQUEST['uuid']);

	$xml = new SimpleXMLElement('<podbird status="ok"></podbird>');
	respond($xml);
}

function method_podcast_unsubscribe() {
	if (!isset($_REQUEST['feed'])) {
		die("Feed parameter isn't set");
	}
	if (!isset($_REQUEST['uuid'])) {
		die("UUID parameter isn't set");
	}

	$user = get_user();
	$user->unsubscribe($_REQUEST['feed'], $_REQUEST['uuid']);

	$xml = new SimpleXMLElement('<podbird status="ok"></podbird>');
	respond($xml);
}

function method_user_getCommands() {
	if (!isset($_REQUEST['uuid'])) {
		die("UUID parameter isn't set");
	}

	$user = get_user();
	$commands = $user->getCommands($_REQUEST['uuid']);

	$xml = new SimpleXMLElement('<podbird status="ok"></podbird>');
	$root = $xml->addChild('commands', null);
	foreach ($commands as $command) {
		$feed_node = $root->addChild('command', $command);
	}

	respond($xml);
}

function method_user_getRecommendations() {
	$user = get_user();
	$recommendations = $user->getRecommended();

	$xml = new SimpleXMLElement('<podbird status="ok"></podbird>');

	$root = $xml->addChild('recommendations', null);
	foreach ($recommendations as $podcast) {
		add_podcast_node($root, $podcast);
	}

	respond($xml);
}

function method_user_getSubscriptions() {
	$user = get_user();
	$subscriptions = $user->subscriptions();

	$xml = new SimpleXMLElement('<podbird status="ok"></podbird>');

	$root = $xml->addChild('subscriptions', null);
	foreach ($subscriptions as $sub) {
		add_podcast_node($root, $sub);
	}

	respond($xml);
}

function method_user_setToken() {
	if (!isset($_REQUEST['token'])) {
		die("Token parameter isn't set");
	}
	if (!isset($_REQUEST['uuid'])) {
		die("UUID parameter isn't set");
	}

	$user = get_user();
	$user->setToken($_REQUEST['token'], $_REQUEST['uuid']);

	$xml = new SimpleXMLElement('<podbird status="ok"></podbird>');
	respond($xml);
}

function get_user() {
	global $adodb;

	if (!isset($_REQUEST['user'])) {
		die("User parameter isn't set");
	}
	if (!isset($_REQUEST['password'])) {
		die("Password parameter isn't set");
	}

	$user = new User($_REQUEST['user']);
	if (hash('sha256', $_REQUEST['password'] . $user->salt) == $user->password) {
		return $user;
	} else {
		die("E_AUTH");
	}
}

function add_podcast_node($root, $podcast) {
	$feed_node = $root->addChild('feed', null);
	$feed_node->addAttribute('name', $podcast->name);
	$feed_node->addAttribute('artist', $podcast->artist);
	$feed_node->addAttribute('website', $podcast->website);
	$feed_node->addAttribute('url', $podcast->feed);
	$feed_node->addAttribute('image', $podcast->image);
	return $feed_node;
}

function respond($xml) {
	if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'json') {
		json_response(json_encode($xml));
	} else {
		xml_response($xml->asXML());
	}
}

function xml_response($xml) {
	header('Content-Type: text/xml');
	echo $xml;
}

function json_response($data) {
	header('Content-Type: application/json; charset=utf-8');
	if (isset($_REQUEST['callback'])) {
		print($_REQUEST['callback'] . '(' . $data . ');');
	} else {
		print($data);
	}
}

function get_with_default($param, $default) {
	if (isset($_REQUEST[$param])) {
		return $_REQUEST[$param];
	} else {
		return $default;
	}
}

if (!isset($_REQUEST['method'])) {
	die('No method requested');
}

$_REQUEST['method'] = strtolower($_REQUEST['method']);
if (!isset($method_map[$_REQUEST['method']])) {
	die("Invalid method");
}

$method = $method_map[$_REQUEST['method']];
$method();
