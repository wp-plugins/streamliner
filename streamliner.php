<?php
/*
Plugin Name: Streamliner Embed
Plugin URI: http://streamliner.co
Description: Inserts an embedded streamline into a Wordpress blog entry. 
Version: 0.1
Author: Chris Fong
Author URI: http://streamliner.co
License: GPL2
*/

include (dirname (__FILE__).'/plugin.php');

class Streamliner extends Streamliner_Plugin
{
	function Streamliner() {
		$this->register_plugin ('streamliner', __FILE__);
		
		$this->add_filter ('the_content');
		$this->add_action ('wp_head');
	}
	
	function wp_head() {
	}
	
	function replace($matches) {
		preg_match("/([^ ]+)(?: ([0-9]+))?(?: ([0-9]+))?/", $matches[1], $parts);
		if(count($parts) > 0) {
			
			// Match host and streamline id
			$url  = $parts[1];
			preg_match("/(http:\/\/[^\/]+)\/s\/([a-zA-Z0-9]+)\/?/", $url, $url_matches);
			if(count($url_matches) == 0) {
				return '';
			}
			$host = $url_matches[1];
			$streamline_id = $url_matches[2];
			
			// Width and height are optional
			$query = "";
			if(count($parts) > 2) {
				$query .= "?width=".$parts[2];
				if(count($parts) > 3) {
					$query .= "&height=".$parts[3];
				}
			}

			// Fetch streamline thumbnail markup from Streamliner
			$sl_wp_url = $host."/wordpress/".$streamline_id."/".$query;
			$cache_group = "STREAMLINER";
			$streamline = wp_cache_get($sl_wp_url, $cache_group);
			if(false == $streamline) {
				$streamline = file_get_contents($sl_wp_url);
				wp_cache_set($sl_wp_url, $streamline, $cache_group, 60*60); // 1 hour
			}
			return $streamline;
		}
		
		return '';
	}

	function the_content($text) {
	  return preg_replace_callback ("@(?:<p>\s*)?\[streamliner\s*(.*?)\](?:\s*</p>)?@", array (&$this, 'replace'), $text);
	}
}

wp_enqueue_style(
    "jquery.fancybox", WP_PLUGIN_URL."/streamliner/fancybox/jquery.fancybox-1.3.4.css", 
    false, "1.3.1");
wp_enqueue_script("jquery");
wp_enqueue_script(
  "jquery.fancybox", WP_PLUGIN_URL."/streamliner/fancybox/jquery.fancybox-1.3.4.pack.js", 
  array("jquery"), "1.3.1",1);	
$streamliner = new Streamliner;
?>
