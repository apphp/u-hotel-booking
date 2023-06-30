<?php

/***
 *  Class RSSFeed
 *  -------------- 
 *  Description : encapsulates rss feed properties
 *  Written by  : ApPHP
 *  Version     : 1.0.2
 *  Updated	    : 09.09.2013
 *	Differences : no
 *  
 *	Feed Validator:  http://validator.w3.org/feed/
 *	
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct			  	CleanTextRss 
 *	__destruct              UpdateFeeds
 *							SetType
 *							SetChannel
 *							SetImage
 *							SetItem
 *							OutputFeed
 *							SaveFeed
 *
 *  1.0.2
 *      - fixed & issue for <channelTitle>
 *      -
 *      -
 *      -
 *      -
 *  1.0.1
 *  	- preg_replace('/&/', ' ', $text);
 *  	- improved SaveFeed()
 *  	- added UpdateFeeds()
 *  	- <author> replaced with site admin email
 *  	- fixed &amp; issue for titles in all RSS types
 *	
 *	
 **/

class RSSFeed {

    private static $channelUrl = '';
    private static $channelTitle = '';
    private static $channelDescription = '';
    private static $channelLang = '';
    private static $channelCopyright = '';
    private static $channelDate = '';
	private static $channelAuthor = '';
    private static $channelCreator = '';
    private static $channelSubject = '';
	
	private static $rssType = 'rss1';
    
    private static $imageUrl = '';

    private static $arrItems = array();
    private static $countItems = 0;
	
	private static $fileName = 'feeds/rss.xml';
    
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{

    }
    
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }
	
	/**
	 * Sets RssFeed type
	 * @param $type - type
	 */ 
	public static function SetType($type = '')
	{
		if($type) self::$rssType = $type;
	}

	/**
	 * Sets Channel
	 * @param $url
	 * @param $title
	 * @param $description
	 * @param $lang
	 * @param $copyright
	 * @param $creator
	 * @param $subject
	 */
    public static function SetChannel($url, $title, $description, $lang, $copyright, $creator, $subject)
	{
        self::$channelUrl = $url;
        self::$channelTitle = str_replace('&', '&amp;', $title);
        self::$channelDescription = $description;
        self::$channelLang = $lang;
        self::$channelCopyright = $copyright;
        if(self::$rssType == 'rss1'){
			self::$channelDate=date('Y-m-d').'T'.date('H:i:s').'+02:00';
		}elseif(self::$rssType == 'rss2'){
			self::$channelDate=date('D, d M Y H:i:s T');
		}elseif(self::$rssType == 'atom'){
			self::$channelDate=date('Y-m-d').'T'.date('H:i:sP');
		}else{
			self::$channelDate=date('Y-m-d').'T'.date('H:i:sT');
		}
		self::$channelCreator = $creator;
		self::$channelAuthor = $creator;
        self::$channelSubject = $subject;
    }
    
	/**
	 * Sets Image
	 * @param $url
	 */
    public static function SetImage($url)
	{
        self::$imageUrl=$url;
    }
    
	/**
	 * Sets Item
	 * @param $url
	 * @param $title
	 * @param $description
	 */
    public static function SetItem($url, $title, $description, $pub_date)
	{
        self::$arrItems[self::$countItems]['url']=$url;
        self::$arrItems[self::$countItems]['title']=$title;
        self::$arrItems[self::$countItems]['description']=$description;
		self::$arrItems[self::$countItems]['pub_date']=$pub_date;
        self::$countItems++;    
    }
    
	/**
	 * Returns Feed
	 */
    public static function OutputFeed()
	{
		$nl = "\n";
		
		if(self::$rssType == 'atom'){
		// RSS Atom	

			$output =  '<?xml version="1.0" encoding="utf-8"?>'.$nl;
			$output .= '<feed xmlns="http://www.w3.org/2005/Atom">'.$nl;			
			$output .= '<title>'.self::$channelTitle.'</title>'.$nl;
			//<subtitle>A subtitle.</subtitle>
			$output .= '<link href="'.self::$channelUrl.'" rel="self" />'.$nl;
			$output .= '<link href="'.str_replace('feeds/rss.xml', '', self::$channelUrl).'" />'.$nl;
			$output .= '<id>'.self::$channelUrl.'</id>'.$nl;
			$output .= '<updated>'.self::$channelDate.'</updated>'.$nl;
			$output .= '<author>'.$nl;
			$output .= '<name>'.self::$channelAuthor.'</name>'.$nl;
			$output .= '</author>'.$nl;
			#<id>tag:google.com,2005-10-15:/support/jobs</id>
			for($k=0; $k < self::$countItems; $k++) {
				$output .= '<entry>'.$nl;
				$output .= '<title>'.str_replace('&', '&amp;', self::$arrItems[$k]['title']).'</title>'.$nl;
				$output .= '<link href="'.str_replace('&', '&amp;', self::$arrItems[$k]['url']).'" />'.$nl;
				$output .= '<id>'.str_replace('&', '&amp;', self::$arrItems[$k]['url']).'</id>'.$nl;
				$output .= '<summary>'.self::$arrItems[$k]['description'].'</summary>'.$nl;
				#<id>tag:google.com,2005-10-15:/support/jobs/hr-analyst</id>
				#<issued>2005-10-13T18:30:02Z</issued>
				$output .= '<updated>'.date('Y-m-d', strtotime(self::$arrItems[$k]['pub_date'])).'T'.date('H:i:sP', strtotime(self::$arrItems[$k]['pub_date'])).'</updated>'.$nl;
				$output .= '</entry>'.$nl;			
			}
			$output .= '</feed>'.$nl;			
		
		}elseif(self::$rssType == 'rss2'){
		// RSS 2.0
		
			$output =  '<?xml version="1.0" encoding="utf-8"?>'.$nl;
			$output .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'.$nl;
			$output .= '<channel>'.$nl;
			$output .= '<atom:link href="'.self::$channelUrl.'" rel="self" type="application/rss+xml" />'.$nl;
			$output .= '<title>'.self::$channelTitle.'</title>'.$nl;
			$output .= '<link>'.self::$channelUrl.'</link>'.$nl;
			$output .= '<description>'.self::$channelDescription.'</description>'.$nl;
			$output .= '<language>'.self::$channelLang.'</language>'.$nl;
			$output .= '<copyright>'.self::$channelCopyright.'</copyright>'.$nl;
			$output .= '<pubDate>'.self::$channelDate.'</pubDate>'.$nl;
			///$output .= '<lastBuildDate>'.self::$channelDate.'</lastBuildDate>'.$nl;
			$output .= '<image>'.$nl;
			$output .= '<url>'.self::$imageUrl.'</url>'.$nl;
			$output .= '<title>'.self::$channelTitle.'</title>'.$nl;
			$output .= '<link>'.self::$channelUrl.'</link>'.$nl;
			$output .= '</image>'.$nl;
			for($k=0; $k < self::$countItems; $k++) {
				$output .= '<item>'.$nl;
				$output .= '<title>'.str_replace('&', '&amp;', self::$arrItems[$k]['title']).'</title>'.$nl;
				$output .= '<link>'.str_replace('&', '&amp;', self::$arrItems[$k]['url']).'</link>'.$nl;
				$output .= '<description>'.self::$arrItems[$k]['description'].'</description>'.$nl;
				$output .= '<author>'.self::$channelCreator.'</author>'.$nl;
				$output .= '<guid>'.str_replace('&', '&amp;', self::$arrItems[$k]['url']).'</guid>'.$nl;
				$output .= '<pubDate>'.date('D, d M Y H:i:s T', strtotime(self::$arrItems[$k]['pub_date'])).'</pubDate>'.$nl;
				$output .= '</item>'.$nl;
			};
			$output .= '</channel>'.$nl;
			$output .= '</rss>'.$nl;			
		}else{
		// RSS 1.0
		
			// encoding='iso-8859-1'
			$output =  '<?xml version="1.0" encoding="utf-8"?>'.$nl;
			$output .= '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:taxo="http://purl.org/rss/1.0/modules/taxonomy/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:syn="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/" xmlns:feedburner="http://rssnamespace.org/feedburner/ext/1.0">'.$nl;
			$output .= '<channel rdf:about="'.self::$channelUrl.'">'.$nl;
			$output .= '<title>'.str_replace('&', '&amp;', self::$channelTitle).'</title>'.$nl;
			$output .= '<link>'.self::$channelUrl.'</link>'.$nl;
			$output .= '<description>'.self::$channelDescription.'</description>'.$nl;
			$output .= '<dc:language>'.self::$channelLang.'</dc:language>'.$nl;
			$output .= '<dc:rights>'.self::$channelCopyright.'</dc:rights>'.$nl;
			$output .= '<dc:date>'.self::$channelDate.'</dc:date>'.$nl;
			$output .= '<dc:creator>'.self::$channelCreator.'</dc:creator>'.$nl;
			$output .= '<dc:subject>'.self::$channelSubject.'</dc:subject>'.$nl;
			$output .= '<items>'.$nl;
			$output .= '<rdf:Seq>';
			for($k=0; $k<self::$countItems; $k++) {
				$output .= '<rdf:li rdf:resource="'.str_replace('&', '&amp;', self::$arrItems[$k]['url']).'"/>'.$nl;
			};
			$output .= '</rdf:Seq>'.$nl;
			$output .= '</items>'.$nl;
			$output .= '<image rdf:resource="'.self::$imageUrl.'"/>'.$nl;
			$output .= '</channel>'.$nl;
			for($k=0; $k < self::$countItems; $k++) {
				$output .= '<item rdf:about="'.str_replace('&', '&amp;', self::$arrItems[$k]['url']).'">'.$nl;
				$output .= '<title>'.str_replace('&', '&amp;', self::$arrItems[$k]['title']).'</title>'.$nl;
				$output .= '<link>'.str_replace('&', '&amp;', self::$arrItems[$k]['url']).'</link>'.$nl;
				$output .= '<description>'.self::$arrItems[$k]['description'].'</description>'.$nl;
				$output .= '<feedburner:origLink>'.str_replace('&', '&amp;', self::$arrItems[$k]['url']).'</feedburner:origLink>'.$nl;
				$output .= '</item>'.$nl;
			};
			$output .= '</rdf:RDF>'.$nl;			
		}
        
        return $output;
    }

  	/***
	 * Saves Feed
	 */
    public static function SaveFeed()
	{
		$handle = @fopen(self::$fileName,'w+');
		if($handle){
			@fwrite($handle, self::OutputFeed());
			@fclose($handle);
			$result = '';
		}else{
			$result = _RSS_FILE_ERROR;		
		}
		return $result;
    }

	/**
	 * Cleans text from all formating
	 * @param $text
	 */
	public static function CleanTextRss($text)
	{
		// $text = preg_replace( "']*>.*?'si", '', $text );
		/* Remove this line to leave URL's intact */
		/* $text = preg_replace( '/]*>([^<]+)<\/a>/is', '\2 (\1)', $text ); */
		$text = preg_replace('//', '', $text);
		$text = preg_replace('/{.+?}/', '', $text);
		$text = preg_replace('/ /', ' ', $text);
		//$text = preg_replace('/&/', ' ', $text);
		$text = preg_replace('/"/', ' ', $text);
		/* add the second parameter to strip_tags to ignore the tag for URLs */
		$text = strip_tags($text, '');
		$text = stripcslashes($text);
		$text = htmlspecialchars($text);
		//$text = htmlentities( $text );
		
		return $text;
	}
	
	/**
	 *  Updates Feeds
	 */
	public static function UpdateFeeds()
	{
		global $objSettings, $objSiteDescription;

		$default_lang = Languages::GetDefaultLang();
		$current_rss_ids = $objSettings->GetParameter('rss_last_ids');
		$rss_ids = '';
		
		self::SetType($objSettings->GetParameter('rss_feed_type'));		
		self::SetChannel(APPHP_BASE.'feeds/rss.xml', $objSiteDescription->GetParameter('header_text'), $objSiteDescription->GetParameter('tag_description'), 'en-us', '(c) copyright', $objSettings->GetParameter('admin_email'), $objSiteDescription->GetParameter('tag_description'));
		self::SetImage(APPHP_BASE.'images/icons/logo.png');
		
		$all_news = News::GetAllNews('previous', $default_lang);		
		for($i=0; $i < $all_news[1] && $i < 10; $i++){					
			$rss_ids .= (($i > 0) ? '-' : '').$all_news[0][$i]['id'];
		}
		
		// check if there difference between RSS IDs, so we have to update RSS file		
		if($current_rss_ids != $rss_ids){
			for($i=0; $i < $all_news[1] && $i < 10; $i++){					
				$rss_text = RSSFeed::CleanTextRss(strip_tags($all_news[0][$i]['body_text']));
				if(strlen($rss_text) > 512) $rss_text = substr_by_word($rss_text, 512).'...';
				#$rss_text = htmlentities($post_text, ENT_COMPAT, 'UTF-8');
				self::SetItem(APPHP_BASE.'index.php?page=news&nid='.$all_news[0][$i]['id'], $all_news[0][$i]['header_text'], $rss_text, $all_news[0][$i]['date_created']);
			}		
			$objSettings->UpdateFields(array('rss_last_ids'=>$rss_ids));				
		}		
		
		return self::SaveFeed();
	}

}
