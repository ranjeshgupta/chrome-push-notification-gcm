<?php
	//http://localhost/gcm/
	//http://localhost/gcm/serviceworker.js
	//http://localhost/gcm/gcm.php/?push=1
	//http://localhost/gcm/json-data.php
	
	
	//Post message to GCM when submitted
	$pushStatus = "GCM Status Message will appear here";	
	if(!empty($_GET["push"])) {
		//$gcmRegID  = file_get_contents("GCMRegId.txt");
		
		$gcmRegIds = array();
		$gcmRegIds[] = "fqyZUvL9RNE:APA91bEByKtoaSxw5szTwXaWeOgM71EuNP9siSdCEhiZ0ST8K3pxTFxON6eFvVFSj24j9jMPBSNSvu3Rr6l7EX-aDRsA5ztydSW3v2eKRI1kvh9R2_0apgnHon_LVR6cDSe23YdRhLmf";
		$gcmRegIds[] = "dz8h7jC3cVA:APA91bEyjhJNQhyUOWBuEsWgXBgzMCC5TgtRHO8y5-SwXin_MpNkcmZf6ChfSWSyZOh5jrwWqMJMyTUe6eDRnwO3NzG4KgIIW1SElqfvwRR6FYfaYll4QuzPSY5zsaSykTS3oJM2DgYQ";

		$message = array(
							"title"=>"My gcm title",
							"body"=>"hi there msg from gcm",
							"icon"=>"/gcm/images/push-icon.png",
							"url"=>"/info.php"
						);
		put_latest_gcm_json($message);
		$pushStatus = sendMessageThroughGCM($gcmRegIds, $message);
		echo $pushStatus ."<br>";
	}
	
	function put_latest_gcm_json($message){
		file_put_contents("GcmJson.txt", json_encode($message));
		echo "Writing json to text file done.<br>";
	}
	
	//Generic php function to send GCM push notification
   function sendMessageThroughGCM($registatoin_ids, $message) {
		//Google cloud messaging GCM-API url
        $url = 'https://android.googleapis.com/gcm/send';
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );
		
		// Update your Google Cloud Messaging API Key
		define("GOOGLE_API_KEY", "AIzaSyD1fz_RHNGniz_LSr__o9QMBm3qiMdmxTQ"); 		
        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);	
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);				
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
	
	//http://deanhume.com/home/blogpost/push-notifications-on-the-web---google-chrome/10128
//http://www.androidhive.info/2012/10/android-push-notifications-using-google-cloud-messaging-gcm-php-and-mysql/
//https://developers.google.com/web/updates/2015/03/push-notifications-on-the-open-web?hl=en
?>
