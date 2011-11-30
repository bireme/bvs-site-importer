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
?>