<?php
/* BVS Site Importer - 2011
 * 
 * This script imports the XML data of BVS-Site 5.x to a XML_RPC structure. 
 * This structure can be imported in a Wordpress instalation with BVS-Site and
 * Multi Language Framework plugins.
 * 
 * Instructions:
 * - Sets the path for XML-BVS-Site directory in the $XML_DIRECTORY
 * - Sets the language that you wants to make import in $LANGUAGE (in bvs-sites with
 *   more than one idiom, please do the process more times)
 *
 * More information in:
 * - http://github.com/bireme/bvs-site-wp-plugin
 * - http://github.com/bireme/wp-multi-language-framework
 */

// XML DIRECTORY that contains items
$XML_DIRECTORY = '/home/moa/project/bireme/vhost/bvsms/bases/site/xml';

if(!file_exists($XML_DIRECTORY)) {
	die("Path does not exists.");
}

// Language (pt | es | en)
$LANGUAGE = "pt";

require_once(dirname(__FILE__) . '/functions.php');

$items = array();
foreach(glob($XML_DIRECTORY . '/' . $LANGUAGE . "/??.xml") as $file) {
	$doc = new DOMDocument();
	$doc->load($file);

	$typename = $doc->documentElement->tagName;

	foreach($doc->getElementsByTagName($typename) as $type) {
		
		foreach($type->attributes as $attr) {	
			$items[$typename]['attr'][$attr->name] = $attr->value;
		}

		$structure = $type->getElementsByTagName("item");

		foreach($structure as $item) {

			// component id
			$id_collection = $items[$typename]['attr']['id'];
		
			foreach($item->attributes as $attr) {	
				$tmp[$attr->name] = $attr->value;
			}
			
			// item id
			$id_tmp = $tmp['id'];

			if($item->hasChildNodes()) {
				$tmp['content'] = $item->firstChild->nodeValue;

			}
			
			$tmp['parent_id'] = 0;			

			
			if($item->hasChildNodes()) {

				foreach($item->getElementsByTagName('description') as $node) {
					 
					$tmp[$node->tagName] = $node->nodeValue;	
				}
			} 

			if(isset($tmp['id']) && isset($items[$typename]['attr']['id'])) {

				$tmp['id'] = $id_collection . 0 . $id_tmp;
				//$tmp['id'] = $id_collection * 10 + $id_tmp;
			}

			// pega os filhos
			$tmp['childs'] = get_child_ids($item);

			// trata os ids dos filhos
			foreach($tmp['childs'] as $key => $child) {
				$tmp['childs'][$key] = $id_collection . 0 . $child;
			}

			$items[$typename][$tmp['id']] = $tmp;
		} 
		
		
	}


	//break;
}

// agora itera pegando os filhos e colocando os aprents ids corretos
foreach($items as $typename => $type) {
	
	foreach($type as $item_id => $item) {
	
		if(isset($item['childs']) && count($item['childs']) > 0) {
			
			foreach($item['childs'] as $child) {
				
				if(isset($type[$child])) {

					$items[$typename][$child]['parent_id'] = $item['id'];
				}
			}
		}
	}
}

if(isset($_REQUEST['debug'])) {
	
	print '<pre>';
	print_r($items);die;
}

$parsed_items = array();
foreach($items as $label => $item) {
	
	foreach($item as $itemnumber => $child) {
		
		$tmp = array();

		if($label == 'collection' && $itemnumber != "attr") {
			foreach($child as $key => $value) {
				
				switch($key) {
					case 'content': $tmp['title'] = $value; break;
					case 'available': $tmp['status'] = $value; break;
					case 'description': $tmp['content'] = $value; break;
					case 'href': $tmp['link'] = $value; break;
					case 'parent_id': $tmp['wp:post_parent'] = $value; break;
					case 'id': $tmp['wp:post_id'] = $value; break;
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

$author = $dom->createElement('wp:author');

foreach(array('wp:author_login' => 'importer', 'wp:author_email' => 'importer@bvs.com') as $key => $value) {
	$item = $dom->createElement("$key", $value);
	$author->appendChild($item);
}

$channel->appendChild($author);

// rss content
foreach($parsed_items as $bvs_item) {
	
	$item = $dom->createElement('item');

	foreach($bvs_item as $key => $value) {
		
		// itens que sao cDATA
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

if(!isset($_REQUEST['debug'])) {
	
	header("Content-Type: text/xml");
	print $dom->saveXML();
	
}

?>
