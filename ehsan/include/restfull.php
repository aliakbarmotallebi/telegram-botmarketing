<?php
class drupalRest {
    
    public function RequestToServer($url , $data='' , $method = '',$bg=false){

		   $curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			if($method=='post'){
		    	$curl_post_data =json_decode($data);
    			curl_setopt($curl, CURLOPT_POST, true);
    			if($bg){
    				curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    			}
    			@curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
			}
			curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);
			$result = $this->_handleResponse($curl);
            curl_close($curl);
            return $result;
	}
	
	
	private function _handleResponse($curl) {
        $response = curl_exec($curl);
        $decode = json_decode($response,true);
        foreach($decode as $contact)
        {
            $contacts[] = $contact;
        }
        
        return $decode;
     }
}

$n  = new drupalRest();
var_dump($n->RequestToServer('http://botmarketing.ir/?q=bot/node/32'));


?>