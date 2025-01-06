<?php

namespace App\Libraries;

/**
 * Description of HTML Library
 *
 * @author https://roytuts.com
 */
class HashidLibrary {
	
	function __construct() {
		require_once APPPATH . "ThirdParty/ThirdParty/Hashids/Hashids.php";
	}
	
	
    public function hashID($init,$length,$char) {
        $mail = new Hashids($init,$length,$char);
        return $mail;
    }
	
}