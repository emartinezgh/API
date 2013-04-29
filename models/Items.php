<?php
class Items extends Phalcon\Mvc\Collection {
	protected static $_fieldMap = array(
		'id'						=> null,
		'name'					=> null,
		'attrs'					=> null,
		'stats'					=> null,
		'icon'					=> null,
		'type'					=> null,
		'quality'				=> null,
		'_created'			=> 'created',
	);
	
	public static function json($params) {
		$data = static::find($params);
		return array_map(function($doc) {
			$json = array();
			foreach(static::$_fieldMap as $idx => $newName) {
				if(isset($doc->$idx) && $doc->$idx != null) {
					if($newName) {
						$json[$newName] = $doc->$idx;																		
					} else {
						$json[$idx] = $doc->$idx;						
					}
				} else {
					$json[$newName?:$idx] = null;
				}
			}
			return $json;
		}, $data);
	}
}