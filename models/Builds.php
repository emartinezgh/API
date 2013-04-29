<?php
class Builds extends Phalcon\Mvc\Collection {
	protected static $_fieldMap = array(
		'id'						=> null,
		'name'					=> null,
		'class'					=> 'heroClass',
		'level'					=> null,
		'hardcore'			=> null,
		'paragon'				=> null,
		'actives'				=> null,
		'passives'			=> null,
		'_characterId'	=> 'bt-id',
		'_characterBt'	=> 'bt-tag',
		'_characterRg'	=> 'bt-srv'
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