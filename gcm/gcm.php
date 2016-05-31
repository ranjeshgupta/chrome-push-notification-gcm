<?php
	//http://localhost/gcm/
	//http://localhost/gcm/serviceworker.js
	//http://localhost/gcm/gcm.php/?push=1
	//http://localhost/gcm/json-data.php
	
	//Add registration_ids for gcm
	if(isset($_POST["register"])) {
		if(isset($_POST["regid"])){
			$reg_id = $_POST["regid"];
		}
		else{
			die("regid not set");
		}
		echo $reg_id;
		
		//use database instead of text file
		//and add regId to table if not exits
		$gcmRegIDs_content  = file_get_contents("GcmRegIds.txt");
		$gcmRegIds = explode("\n", $gcmRegIDs_content);
		$is_reg_id_exists = false;
		foreach($gcmRegIds as $regId){
			if($regId == $reg_id){
				$is_reg_id_exists = true;
				break;
			}
		}
		
		if($is_reg_id_exists===false)
			file_put_contents("GcmRegIds.txt", "\n".$reg_id, FILE_APPEND | LOCK_EX);
	}
	
	//Post message to GCM when submitted	
	if(isset($_GET["push"])) {
		//db will be used to contains registatoin_ids so pick data from table
		//I have implemented it using text file
		$gcmRegIDs_content  = file_get_contents("GcmRegIds.txt");
		$gcmRegIds = explode("\n", $gcmRegIDs_content);
		echo"<pre>";
		print_r($gcmRegIds);
		echo "</pre>";
		$message = array(
							"title"=>"My gcm title",
							"body"=>"hi there msg from gcm",
							"icon"=>"/gcm/images/push-icon.png",
							"url"=>"/info.php"
						);
		put_latest_gcm_json($message);
		$pushStatus = sendMessageThroughGCM($gcmRegIds, $message, 'my_collapsed_key');
		echo $pushStatus ."<br>";
	}
	
	function put_latest_gcm_json($message){
		file_put_contents("GcmJson.txt", json_encode($message));
		echo "Writing json to text file done.<br>";
	}
	
	//Generic php function to send GCM push notification
   function sendMessageThroughGCM($registatoin_ids, $message, $collapse_key) {
		//Google cloud messaging GCM-API url
        $url = 'https://android.googleapis.com/gcm/send';
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'collapse_key' => $collapse_key, //collapsed key so only one msg will arrive when user get online as he/whe was offline at delivery time and got multiple msg
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

//see these urls for info	
//http://deanhume.com/home/blogpost/push-notifications-on-the-web---google-chrome/10128
//http://www.androidhive.info/2012/10/android-push-notifications-using-google-cloud-messaging-gcm-php-and-mysql/
//https://developers.google.com/web/updates/2015/03/push-notifications-on-the-open-web?hl=en
//https://www.design19.org/blog/chrome-push-notifications/
?>
