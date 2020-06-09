<?php
/* Podcast Parser PHP Class
 * PHP Version Required: 5.2 or later
 *
 * Newer refers to items at the top of the feed, Older refers to items at the bottom of the feed.
 *
 * Script version: 0.2 BETA
 *
 * Contributors:
 * - David Grega
 *
 * Priorities:
 * - Ease of Maintenance
 * - Well Documented
 * - Defense against hostile input
 * - Ease of use by third party programmers
 * - Efficiency and Speed
 *
 * License: 3 clause BSD
 */
 
 class PodcastParser {
   private $podcastFileContents;
   private $podcastXML;
   private $podcastURL;
   private $totalFeedItems;
   private $newestEpisodeIndex;
   private $oldestEpisodeIndex;
   const iTunesNamespace = 'http://www.itunes.com/dtds/podcast-1.0.dtd';
   const atomNamespace = 'http://www.w3.org/2005/Atom';
   
   function __construct($URL) {
    /* Preconditions:
	  * $URL is  untrusted input, possibly containing the URL to a podcast feed.
	   */
		# Validate that we have a valid URL
		$URL = filter_var($URL,FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED);
		if ($URL === FALSE) {
			throw new Exception('Invalid URL.  URLs require a scheme (e.g. http://) and hostname (e.g. example.com)');
		} 
	
		# Sanitize data so we only have a URL
		$URL = filter_var($URL, FILTER_SANITIZE_URL);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.
		
		# Passed data firewall, let's make this information available to the entire class
		$this->podcastURL = $URL;
		unset($URL);
	
		# Go ahead and load the Podcast feed now
		$this->podcastFileContents = file_get_contents($this->podcastURL, FILE_TEXT);
		if ($this->podcastFileContents === FALSE) {
			throw new Exception('Failed to open the URL "' . $this->podcastURL .'".  Please verify:
				1) allow_url_fopen = true in your php.ini, 
				2) that this server is connected to the internet, and 
				3) your server firewall is not blocking the remote file access capabilities of PHP');
		}
	 
		if ($this->isValidPodcastFeed() === TRUE) {
			$this->preProcessFeed();
		} else {
			throw new Exception('The document at "' . $this->podcastURL .'" does not appear to be a valid podcast feed.  Please verify: 
				1) it is an XML document, and 
				2) this XML document has enclosure tags in compliance with podcast feed specifications');
		}
		
   }
   
   // Concern: Are there podcast feeds that put newest episodes anywhere other 
   //          than at the top of the feed?  If so, we'll need a sortPodcastItems() function.
   
   private function isValidPodcastFeed() {
     /* Preconditions:
	  * - $this->podcastFileContents has trused XML in it
	  *
	  * Postconditions:
	  * - Return TRUE if we can probably process this feed
	  * - Throw exception if we cannot process this feed
	  */

		# Test for valid, digestable XML
		$this->podcastXML = $this->isValidXML();
		if ($this->podcastXML === FALSE) {
			// If this is not XML, let's not bother going further
			throw new Exception('The document at "' . $this->podcastURL . '" is not valid XML');
		}
		// Note, now podcastXML now contains a SimpleXML object of the raw file.
		
		# Test for Channel Tag
		if (isset($this->podcastXML->channel) === FALSE) {
			throw new Exception('The document at "' . $this->podcastURL . '" does not contain a <channel> tag where appropriate. 
				As a result, this does not appear to be a podcast feed.'); 
		}
		
		# Test for Enclosure Tag(s)
		$counter = 0;
		$foundEnclosure = FALSE;
		
		# Let's go ahead and store the number of podcast items now since this is a frequently used value
		$this->totalFeedItems = count($this->podcastXML->channel[0]->item);
		
		# Loop through all podcast items
		while (($counter < $this->totalFeedItems) AND ($foundEnclosure === FALSE)) {
			if (isset($this->podcastXML->channel[0]->item[$counter]->enclosure['url'])) {
				# Ensure we have enough data in the enclosure tag for processing
				$foundEnclosure = TRUE;
			}
			
			// Loop through all items in the feed as needed.  Ideally, this loop is only executed once.
			$counter++;
		}
		
		if ($foundEnclosure === FALSE) {
			throw new Exception('The document at "' . $this->podcastURL . '" does not contain any appropriate englosure tags where appropriate. 
				As a result, this does not appear to be a podcast feed.'); 			
		}
		
		return TRUE;
   }
   
   private function isValidXML() {
     /* Preconditions:
	  * - $this->podcastFileContents has something in it
	  *
	  * Postconditions:
	  * - Returns FALSE if not valid XML 
	  * - Returns SimpleXML object if valid XML
	  */
		// The LIBXML_NOCDATA option ensures all CDATA tags (commonly used in podcast feeds) are processed into the XML Object
		//	This makes 'SimpleXMLElement' (the object to work with) a required parameter.
		// This process also automatically removes all binary data <!BDATA[ ... ]> content when converting the document into a SimpleXML object 
		$isXML = @simplexml_load_string($this->podcastFileContents, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		#ToDo: Ensure this XML can be trusted.  We are assuming this XML is safe.
	  
	    return $isXML;
   }
   
   private function preProcessFeed(){
     /* Preconditions:
	    * - $this->podcastXML is a simpleXML object
	    * Postconditions:
	    * - $this->podcastXML is a simpleXML object organizated efficiently so our functions can work swiftly with it.
	   */
	   
		# Insert GUID tags for any episode lacking them.  This will likely improve processing of implicit GUIDs.

		$counter = 0;
		$isFirst = TRUE;
		while ($counter < $this->totalFeedItems) {
			if (isset($this->podcastXML->channel[0]->item[$counter]->enclosure['url'])) {
				#  Okay, this is a podcast entry, let's do the GUID thing.
				if (isset($this->podcastXML->channel[0]->item[$counter]->guid)) {
					# Great, GUID is set
					if ($isFirst == TRUE) {
						# Assuming a sorted feed with newest podcasts at the top, the first podcast will be the newest
						$this->newestEpisodeIndex = $counter;
						$isFirst = FALSE;
					}
				} else {
					# Set implicit GUID according to podcast spec (enclosure URL)
					$this->podcastXML->channel[0]->item[$counter]->addChild('guid',$this->podcastXML->channel[0]->item[$counter]->enclosure['url']);
				}
				
				# Assuming a sorted feed with newest podcasts at the top, keep setting this to point to the oldest episode we find.
				$this->oldestEpisodeIndex = $counter;
			}
			
			// Loop through all items in the feed as needed.  Ideally, this loop is only executed once.
			$counter++;
		}
   }

   public function getNewestEpisode() {
     /* Preconditions:
	  * - Podcast feed is sorted with newest episodes near the top of the feed
	  *
	  * Postconditions:
	  * - Returns GUID of newest episode
	  */
		$counter = 0;
		while ($counter < $this->totalFeedItems) {
			if (isset($this->podcastXML->channel[0]->item[$counter]->enclosure['url'])) {
				#  We have found our first podcast entry, return the GUID
				return (string) $this->podcastXML->channel[0]->item[$counter]->guid;
			}
			
			// Loop through all items in the feed as needed.  Ideally, this loop is only executed once.
			$counter++;
		}
		
		throw new Exception('No podcast episodes were found in ' . $this->podcastURL);
   }
   
   public function getOldestEpisode() {
     /* Preconditions:
	  * - Podcast feed is sorted with newest episodes near the top of the feed
	  *
	  * Postconditions:
	  * - Returns GUID of oldest episode
	  */
		$counter = $this->totalFeedItems - 1;
		while ($counter >= 0) {
			if (isset($this->podcastXML->channel[0]->item[$counter]->enclosure['url'])) {
				#  We have found our first podcast entry, return the GUID
				return (string) $this->podcastXML->channel[0]->item[$counter]->guid;
			}
			
			// Loop through all items in the feed as needed.  Ideally, this loop is only executed once.
			$counter--;
		}
		
		throw new Exception('No podcast episodes were found in ' . $this->podcastURL);
	}
	
   private function convertGUIDtoItemIndex($GUID) {
    /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  * - Implicit GUIDs have already been set as if they were set as the GUID tag.
	  *
	  * Postconditions:
	  * - returns the index of the item array where this podcast is found.
	  */
		$GUID = (string) $GUID;
		// A GUID can be any string, so not much to filter for at this time.  Just be sure to compare against known good values only.
	 
		# Loop through every GUID in the file, see if it matches the GUID provided.
		$counter = 0;
		while ($counter < $this->totalFeedItems) {
			# Make certain we are limiting our requests to just podcast items
			if (isset($this->podcastXML->channel[0]->item[$counter]->enclosure['url'])) {
				if($GUID == $this->podcastXML->channel[0]->item[$counter]->guid) {
					return $counter;
				}
			}
			
			// Loop through all items in the feed as needed.  Ideally, this loop is only executed once.
			$counter++;
		}
		
		// Remember, GUID is known invalid at this point so we have reason to be suspicious.
		// Therefore, URLencode GUID just to be safe.  Will not affect legit output, but will cause issues 
		//	for illigimate input
		throw new Exception('There is no podcast in the feed "' . $this->podcastFeedURL . '" containing a GUID of "' . urlencode($GUID) . '".');
   }
	
   public function getShowNotesForEpisode($GUID) {
     /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  */
		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.		
	  
		# Warning, description tag will likely contain arbitary HTML
		return (string) $this->podcastXML->channel[0]->item[$itemIndex]->description;
   }
   
   public function getEpisodeLengthTime($GUID) {
     /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  * - Valid Enclosure Tag specifying this information
	  * - One enclosure tag per episode
	  *
	  * Postconditions:
	  * - Returns the raw string of the episode length.  
	  *   This is usually of format mm:ss or hh:mm:ss in human-readable format
	  * - Returns nothing if information is not found.
	  */
	  
		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.		
	  
		# Warning, description tag will likely contain arbitary HTML
		return (string) @$this->podcastXML->channel[0]->item[$itemIndex]->children(self::iTunesNamespace)->duration;
   }

   public function getEpisodeTitle($GUID) {
     /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  * - Valid Enclosure Tag specifying this information
	  * - One enclosure tag per episode
	  *
	  * Postconditions:
	  * - Returns the title of the podcast episode with the given GUID
	  */
	  
		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.		
	  
		# Warning, description tag will likely contain arbitary HTML
		return (string) @$this->podcastXML->channel[0]->item[$itemIndex]->title;
   }   

   public function getEpisodeExplicitRating($GUID) {
     /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  * - Valid Enclosure Tag specifying this information
	  * - One enclosure tag per episode
	  *
	  * Postconditions:
	  * - Returns the contents of the iTunes explicit tag for this episode
	  */
	  
		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.		
	  
		# Explicit is not a binary value, hence not using boolean
		return (string) @$this->podcastXML->channel[0]->item[$itemIndex]->children(self::iTunesNamespace)->explicit;
   }
   
   public function getEpisodeDownloadURL($GUID) {
     /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  * - Valid Enclosure Tag specifying this information
	  * - One enclosure tag per episode
	  *
	  * Postconditions:
	  * - Returns the URL where the episode of this show can be downloaded
	  */
	  
		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.		
	  
		return (string) @$this->podcastXML->channel[0]->item[$itemIndex]->enclosure['url'];
   }
   
   public function getFileSizeInBytesForEpisode($GUID) {
     /* Preconditions:
	  * - Valid Enclosure Tag specifying this information
	  * - One enclosure tag per episode
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  *
	  * Postconditions:
	  * - Returns the file size of this episode in bytes (standard)
	  */
	  
		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.		
	  
		# Warning, description tag will likely contain arbitary HTML
		return (string) $this->podcastXML->channel[0]->item[$itemIndex]->enclosure['length'];
	  
		// Concern: If this becomes an issue, we may want to implement checking file 
		//          size manually if file size is not specified.  Non-Standard.
   }
   
   public function getPodcastFeedImageURL() {
     /* Preconditions:
	  * - $this->podcastXML contains a SimpleXML object representing the podcast feed
	  * Postconditions:
	  * - Returns the URL of the podcast feed image.
	  */
		###############################
		
		# Generic Podcast Feed
		$feedImageURL = $this->podcastXML->channel[0]->image->url;

		# Compensate for iTunes weirdness
		if (empty($feedImageURL) || $feedImageURL == '/') {
			#channel/itunes:image[href]
			$feedImageURL = @$this->podcastXML->channel[0]->children(self::iTunesNamespace)->image->attributes('');
			$feedImageURL = $feedImageURL['href'];
		}
		
        # Always trim URLs
        $feedImageURL = trim($feedImageURL);		

		return (string) $feedImageURL;
   }
   
   public function getPodcastWebsite() {
    /* Postconditions:
	 * - Returns the URL of the podcast's website
	 */
		# Try primary location first
		$link = $this->podcastXML->channel[0]->link;
		
		if (empty($link)) {
			# Try secondary location
			$link = $this->podcastXML->channel[0]->image->link;
		}
		
		return (string) $link;
   }
  
   public function getPodcastTitle() {
	return (string) $this->podcastXML->channel[0]->title;
   }

   public function getPodcastAuthor() {
	return (string) $this->podcastXML->channel[0]->children(self::iTunesNamespace)->author;
   }

   public function getPodcastDescription() {
	return (string) $this->podcastXML->channel[0]->description;
   }



   public function getNewestFeedURL() {
    /* Preconditions:
	 * - $this->podcastXML contains a SimpleXML object representing a valid podcast feed
	 * Postconditions:
	 * - Returns the URL noted in the feed as being the newest feed URL
	 * - If this URL is not noted in the feed, return current URL instead
	 * - If there is no new URL, return the current URL of the feed instead.
	 */
	 
		# Default to no known new URL
		$newestURL = '';

		# Try iTunes first		
		$newestURL = @$this->podcastXML->channel[0]->children(self::iTunesNamespace)->{'new-feed-url'};
		
		if(empty($newestURL)) {
			# Try Atom as a backup
			$newestURL = @$this->podcastXML->channel[0]->children(self::atomNamespace)->link->attributes('');
			$newestURL = @$newestURL['href'];
		}

		if (empty($newestURL)) {
			# If we don't have a new URL, return the URL of our current feed
			return (string) $this->podcastURL;
		} else {
			# If we got a new feed URL, return that
			return (string) $newestURL;
		}	 
	}
   
   public function getLastUpdated() {
    /* Postconditions
	 * - Returns the date and time this feed was most recently updated as a PHP DateTime object
	 * - Does not assume adherence to podcast specification  
	 */
		$lastUpdatedString = '';
		$lastUpdatedDateTime = '';
		
		# Try with the most reliable tag, the last time a podcast was actually published
		$lastUpdatedString = @$this->podcastXML->channel[0]->pubDate;
		if (empty($lastUpdatedString)) {
			# Next, let's try the publication date of the oldest podcast
			$lastUpdatedString = @$this->podcastXML->channel[0]->item[$this->oldestEpisodeIndex]->pubDate;
			if(empty($lastUpdatedString)) {
				# Let's go with the crappiest possibility, the last time the podcast was modified according to the XML
				$lastUpdatedString = @$this->podcastXML->channel[0]->lastBuildDate;
				if(empty($lastUpdatedString)) {
					throw new Exception('This podcast feed does not have any appropriate information about when it was published');
				}
			} 
		} 
		
		$lastUpdatedString = substr((string) $lastUpdatedString, (strpos((string) $lastUpdatedString, ',') + 1));
		$lastUpdatedDateTime = new DateTime($lastActiveDate);
	
		return $lastUpdatedDateTime;
   }
   
   public function getNewestEpisodeGUID() {
    /* Postconditions:
	  * - GUID of the first podcast episode of the feed is returned
	  */
		return (string) $this->podcastXML->channel[0]->item[$this->newestEpisodeIndex]->guid;
   }
   
   public function getOldestEpisodeGUID() {
    /* Postconditions:
	  * - GUID of the final episode in the podcast feed is returned
	  */
		return (string) $this->podcastXML->channel[0]->item[$this->oldestEpisodeIndex]->guid;
   }

   public function getOlderEpisodeGUID($GUID) {
    /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  *
	  *  Postconditions:
	  * - GUID of the next older episode is returned.  
	  * - If there is no older episode, return NULL
	  */
		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.	

		# Check to see if there is an episode after this (podcasts only)
		if ($itemIndex < $this->oldestEpisodeIndex) {
			// Start with next item in feed
			$counter = $itemIndex + 1;
			
			// Now keep going forward until we hit a podcast entry
			while ($counter <= $this->oldestEpisodeIndex) {
				# Make certain we are limiting our requests to just podcast items
				if (isset($this->podcastXML->channel[0]->item[$counter]->enclosure['url'])) {
					return (string) $this->podcastXML->channel[0]->item[$counter]->guid;
				}
				
				// Loop through all items in the feed as needed.  Ideally, this loop is only executed once.
				$counter++;
			}
		}
		
		return NULL;
   }
   
   public function getNewerEpisodeGUID($GUID) {
    /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  *
	  *  Postconditions:
	  * - GUID of the next older episode is returned.  
	  * - If there is no older episode, return NULL
	  */
		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.	

		# Check to see if there is an episode before this (podcasts only)
		if ($itemIndex > $this->newestEpisodeIndex) {
			// Start with previous item
			$counter = $itemIndex - 1;
			
			// Now keep going backwards until we hit a podcast entry
			while ($counter >= $this->newestEpisodeIndex) {
				# Make certain we are limiting our requests to just podcast items
				if (isset($this->podcastXML->channel[0]->item[$counter]->enclosure['url'])) {
					return (string) $this->podcastXML->channel[0]->item[$counter]->guid;
				}
				
				// Loop through all items in the feed as needed.  Ideally, this loop is only executed once.
				$counter--;
			}
		}
		
		return NULL;
   }
   
   public function getShowNotesURLForEpisode($GUID) {
     /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  *
	  * Postconditions:
	  * - Returns the show notes URL for this episode (identified by its GUID)
	  */

		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.		
	  
		# Warning, description tag will likely contain arbitary HTML
		return getDescriptionTagForItemID($itemIndex);
	  	  
   }
   
   private function getDescriptionTagForItemID($ID) {
    /* Preconditions:
	  * $ID is a trusted integer representing the item index in the $this->podcastXML->channel[0]->item array
	  *
	  * Postconditions:
	  * - Return the contents of the Description Tag for this item (essentially, provide the show notes)
	  */
		return (string) $this->podcastXML->channel[0]->item[$ID]->link;
   }
   
   public function getLastEpisodeGUID() {
	/* Postconditions:
	 * Return the GUID of the last episode in this feed
	 */
		return (string) $this->podcastXML->channel[0]->item[$this->oldestEpisodeIndex]->GUID;
   }
   
   public function getFirstEpisodeGUID() {
	/* Postconditions:
	 * Return the GUID of the first episode in this feed
	 */
		return (string) $this->podcastXML->channel[0]->item[$this->newestEpisodeIndex]->GUID;
   }   
   
   public function getShowNotesForEpisodeAfter($GUID) {
     /* Preconditions:
	 * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  *
	  * Postconditions:
	  * - Returns the show notes URL for the episode after this episode
	  * - Returns NULL if there is no episode after this episode
	  */     

		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.	

		$nextOlderGUID = $this->getOlderEpisodeGUID($GUID);
		if (empty($nextOlderGUID)) {
			throw new Exception('There are no episodes older than: ' . urlencode($GUID));
		}
		
		return getShowNotesURLForEpisode($nextOlderGUID);
   }
   
   public function getShowNotesForEpisodeBefore($GUID) {
     /* Preconditions:
	  * - GUID is untrusted input, presumably is a valid GUID.  
	  * - Implicit GUIDs allowed (meaning URL as the GUID).
	  *
	  * Postconditions:
	  * - Returns the show notes URL for the episode before this episode
	  * - Returns NULL if there is no episode before this episode
	  */     
	  
		$itemIndex = $this->convertGUIDtoItemIndex($GUID);
		
		# The only code that should be above this line is the data firewall code.
		###################################################################
		# At this point in sequential code processing, all input is now trusted.	

		$nextNewerGUID = $this->getNewerEpisodeGUID($GUID);
		if (empty($nextNewerGUID)) {
			throw new Exception('There are no episodes older than: ' . urlencode($GUID));
		}
		
		return getShowNotesURLForEpisode($nextNewerGUID);
   }   
 }
