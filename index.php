<?php

ini_set('default_charset', 'utf-8');

$dir = dirname(__FILE__) . '/xml';

// dicionário DE-PARA ( "bvs-site" => "wordpress")
$terms_parser = array(
	'collection' => array(
		"content" => "title",
		"available" => "status",
		"description" => "content",
		"href" => "link",
		"lang" => "language",
	),
	
	'community' => array(
		"content" => "title",
		"avaiable" => "status",
		"img" => "thumbnail",
		"href" => "link",
		"description" => "content",
		"lang" => "language",
	),

	'about' => array(
		"content" => "title",
		"avaiable" => "status",
		"href" => "link",
		"description" => "content",
		"lang" => "language",
	),

	'topic' => array(
		"content" => "title",
		"avaiable" => "status",
		"href" => "link",
		"description" => "content",
		"lang" => "language",
	),

	'calls' => array(
		"content" => "title",
		"avaiable" => "status",
		"href" => "link",
		"description" => "content",
		"lang" => "language",
	),

);

$items = array();

foreach(glob($dir . "/??.xml") as $file) {
	$doc = new DOMDocument();
	$doc->load($file);

	$type = $doc->documentElement->tagName;
	$structure = $doc->getElementsByTagName("item");

	foreach($structure as $item) {
	
		foreach($item->attributes as $attr) {	
			$tmp[$attr->name] = $attr->value;
		}

		if($item->hasChildNodes())
			$tmp['content'] = $item->firstChild->nodeValue;
		
		if($item->hasChildNodes()) {

			foreach($item->getElementsByTagName('description') as $node) {
				
				$tmp[$node->tagName] = $node->nodeValue;	
			}
		}

		$items[$type][] = $tmp;
	} 

	//break;
}

$parsed_items = array();
foreach($items as $label => $item) {
	
	foreach($item as $child) {
		
		$tmp = array();

		if($label == 'collection') {
			foreach($child as $key => $value) {
				
				switch($key) {
					case 'content': $tmp['title'] = $value; break;
					case 'available': $tmp['status'] = $value; break;
					case 'description': $tmp['content'] = $value; break;
					case 'href': $tmp['link'] = $value; break;
				}
			}

		}
		$parsed_items[] = $tmp;
	}		
	
}

$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true;
$dom->preserveWhiteSpace = false;

$root = $dom->createElement('rss');

// rss version
$attr = $dom->createAttribute('version');
$attr->value = '2.0';
$root->appendChild($attr);

// WP attrs
$attr = $dom->createAttribute('xmlns:excerpt');
$attr->value = 'http://wordpress.org/export/1.1/excerpt/';
$root->appendChild($attr);

$attr = $dom->createAttribute('xmlns:content');
$attr->value = "http://purl.org/rss/1.0/modules/content/";
$root->appendChild($attr);

$attr = $dom->createAttribute('xmlns:wfw');
$attr->value = "http://wellformedweb.org/CommentAPI/";
$root->appendChild($attr);

$attr = $dom->createAttribute('xmlns:dc');
$attr->value = "http://purl.org/dc/elements/1.1/";
$root->appendChild($attr);

$attr = $dom->createAttribute('xmlns:wp');
$attr->value = "http://wordpress.org/export/1.1/";
$root->appendChild($attr);
		
$channel = $dom->createElement('channel');

// rss header
$header_items = array('link' => '', 'title' => 'BVS-Site Export', 'description' => '', 'language' => 'pt', 'wp:wxr_version' => '1.1', 'generator' => 'http://bireme.org');
foreach($header_items as $key => $value) {
	$key = $dom->createElement("$key", $value);
	$channel->appendChild($key);
}

// rss content
foreach($parsed_items as $bvs_item) {
	
	$item = $dom->createElement('item');

	foreach($bvs_item as $key => $value) {
		
		if(in_array($key, array('content', 'link'))) {
			
			$cdata = $dom->createCDATASection(trim($value));
			$field = $dom->createElement("$key");
			$field->appendChild($cdata);

		} else {
			
			$field = $dom->createElement("$key", "$value");

		}

		$item->appendChild($field);

		foreach(array('wp:post_type' => 'vhl_collection', 'wp:author' => 'admin') as $key => $value) {
			$field = $dom->createElement("$key", "$value");
			$item->appendChild($field);
		}
	}

	$item = $channel->appendChild($item);
}

$root->appendChild($channel);
$dom->appendChild($root);

header("Content-Type: text/xml");
print $dom->saveXML();



?>