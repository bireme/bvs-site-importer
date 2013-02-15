<?

// return the child ids of this Dom Element
// @param $dom: DomElement
// @param $tag_name: Name of tag which has childs
function get_child_ids($dom, $tag_name = "item") {

	$response = array();

	if($dom->hasChildNodes()) {
		foreach($dom->childNodes as $child) {
			
			// pass caso for diferente de tag_name
			if($tag_name != "item" && $child->tagName != $tag_name) {
				continue;
			}

			if($child->hasAttributes()) {
				
				foreach($child->attributes as $attr) {
					if($attr->name == 'id') {
						$response[] = $attr->value;
					}
				}
			}
		}
	}

	return $response;
}

function replace_urls($content) {
	$content = str_replace("&amp;", '&', $content);

	preg_match_all('/php\/level\.php\?lang=pt&component=[0-9]+&item=[0-9]+/', $content, $all_matches);

	// changing the urls
	foreach($all_matches as $matches) {
		foreach($matches as $match) {
			$orig = $match;
			$match = str_replace("php/level.php?lang=pt&component=", "", $match);
			$match = str_replace("&item", "", $match);

			$match = explode("=", $match);
			$id = $match[0] . 0 . $match[1];

			$url = "?p=" . $id;
			$content = str_replace($orig, $url, $content);
		}
	}

	return $content;
} 
?>