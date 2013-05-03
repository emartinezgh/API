<?php
function jsonp_encode($json, $app = false) {
	$data = "";
	if($app && $callback = $app->request->get('callback')) {
		return $callback."(".json_encode($json).")";
	}
	return json_encode($json);
}