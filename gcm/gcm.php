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
		
		if(isset($_POST["browser"])){
			$browser = $_POST["browser"];
		}
		else{
			$browser = "chrome";
		}
		
		$arr_reg_id = array($reg_id, $browser);
		$arr_reg_id = json_encode($arr_reg_id);		
		
		//use database instead of text file
		//and add regId to table if not exits
		$gcmRegIDs_content  = file_get_contents("GcmRegIds.txt");
		$gcmRegIds = explode("\n", $gcmRegIDs_content);
		$is_reg_id_exists = false;
		foreach($gcmRegIds as $regId){
			if($regId == $arr_reg_id){
				$is_reg_id_exists = true;
				break;
			}
		}
		
		if($is_reg_id_exists===false)
			file_put_contents("GcmRegIds.txt", "\n".$arr_reg_id, FILE_APPEND | LOCK_EX);
	}
	
	if(isset($_GET["push"])) {
		$gcmRegIDs_content  = file_get_contents("GcmRegIds.txt");
		$gcmRegIds = explode("\n", $gcmRegIDs_content);
		echo"<pre>";
		print_r($gcmRegIds);
		//echo "</pre>";
		
		$message = array(
							"title"=>"My gcm title",
							"body"=>"hi there msg from gcm",
							"icon"=>"/gcm/images/push-icon.png",
							"url"=>"/info.php"
						);
		put_latest_gcm_json($message);
		$pushStatus = send_push_message($gcmRegIds, $message);
		echo $pushStatus ."<br>";
	}
	
	function send_push_message($subscriptionIDs, $message){
		if (empty($subscriptionIDs)) return FALSE;
		$chs = $sChrome = array();
		$mh = curl_multi_init();
		$aCurlHandles = array();
		foreach ($subscriptionIDs as $subscription){
			$subscription = json_decode($subscription);
			print_r($subscription);
			echo "<br>";
			$i = count($chs);
			switch ($subscription[1]){
				case "firefox":
					echo "firefox<br>";
					$chs[ $i ] = curl_init();
					curl_setopt($chs[ $i ], CURLOPT_URL, "https://updates.push.services.mozilla.com/push/v1/".$subscription[0] );
					curl_setopt($chs[ $i ], CURLOPT_PUT, TRUE);
					curl_setopt($chs[ $i ], CURLOPT_HTTPHEADER, array( "TTL: 86400" ) );
					curl_setopt($chs[ $i ], CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($chs[ $i ], CURLOPT_SSL_VERIFYPEER, FALSE);

					$aCurlHandles[$i] = $chs[ $i ];
					curl_multi_add_handle($mh, $chs[ $i ]);
				break;
				case "chrome":
					echo "chrome<br>";
					$sChrome[] = $subscription[0];
				break;    
			}
		}
		if (!empty($sChrome)){
			$fields = array(
				'registration_ids' => $sChrome,
				'collapse_key' => 'my_collapsed_key',
				'data' => $message,
			);
			$i = count($chs);
			$chs[ $i ] = curl_init();
			curl_setopt($chs[ $i ], CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
			curl_setopt($chs[ $i ], CURLOPT_POST, TRUE);
			curl_setopt($chs[ $i ], CURLOPT_HTTPHEADER, array( "Authorization: key=AIzaSyD1fz_RHNGniz_LSr__o9QMBm3qiMdmxTQ", "Content-Type: application/json" ) );
			curl_setopt($chs[ $i ], CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($chs[ $i ], CURLOPT_SSL_VERIFYPEER, FALSE);			
			curl_setopt($chs[ $i ], CURLOPT_POSTFIELDS, json_encode($fields) );
			
			$aCurlHandles[$i] = $chs[ $i ];
			curl_multi_add_handle($mh, $chs[ $i ]);
		}

		do {
			curl_multi_exec($mh, $running);
			curl_multi_select($mh);
		} while ($running > 0);

		for ($i = 0; $i < count($chs); $i++){
			$html = curl_multi_getcontent($chs[ $i ]); 
			echo $html."<br>";
			
			curl_multi_remove_handle($mh, $chs[ $i ]);
		} 

		curl_multi_close($mh);
	}
	
	function put_latest_gcm_json($message){
		file_put_contents("GcmJson.txt", json_encode($message));
		echo "Writing json to text file done.<br>";
	}
	
	/*
	//Post message to GCM when submitted	
	if(isset($_GET["push"])) {
		//db will be used to contains registatoin_ids so pick data from table
		//I have implemented it using text file
		$gcmRegIDs_content  = file_get_contents("GcmRegIds.txt");
		$gcmRegIds = explode("\n", $gcmRegIDs_content);
		echo"<pre>";
		print_r($gcmRegIds);
		echo "</pre>";
		die();
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
	*/
	/*
	//Generic php function to send GCM push notification
	function sendMessageThroughGCM($registatoin_ids, $message) {
		//Google cloud messaging GCM-API url
        $url = 'https://android.googleapis.com/gcm/send';
        $fields = array(
            'registration_ids' => $registatoin_ids,
			'collapse_key' => 'my_collapsed_key',
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
	*/
	
//see these urls for info	
//http://deanhume.com/home/blogpost/push-notifications-on-the-web---google-chrome/10128
//http://www.androidhive.info/2012/10/android-push-notifications-using-google-cloud-messaging-gcm-php-and-mysql/
//https://developers.google.com/web/updates/2015/03/push-notifications-on-the-open-web?hl=en
//https://www.design19.org/blog/chrome-push-notifications/
?>
