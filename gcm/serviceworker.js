/*
*
*  Push Notifications codelab
*  Copyright 2015 Google Inc. All rights reserved.
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*      https://www.apache.org/licenses/LICENSE-2.0
*
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License
*
*/

// Version 0.1
'use strict';

console.log('Started', self);

self.addEventListener('install', function(event) {
  self.skipWaiting();
  console.log('Installed', event);
});

self.addEventListener('activate', function(event) {
  console.log('Activated', event);
});

var url = "/gcm/json-data.php?param=" + Math.random();
self.addEventListener('push', function(event) {  
  console.log('Received a push message', event);

  event.waitUntil(  
	fetch(url).then(function(response) {  
      if (response.status !== 200) {  
        //if response status != 200 then throw an error as there is an error in json url
        console.log('Looks like there was a problem. Status Code: ' + response.status);  
        throw new Error();  
      }
	  
	  return response.json().then(function(data) {  
        if (data.error) {  
          console.log('The API returned an error.', data.error);  
          throw new Error();  
        }
		
		var title = data.title;
		var body = data.body;
		var icon = data.icon;
		var url = data.url;
		console.log("url for json", url);
		return self.registration.showNotification(title, {  
		  body: body,  
		  icon: icon,  
		  data: {
            url: data.url
          } 
		});
	  });  
    }).catch(function(err) {
	  console.log('Unable to retrieve data', err);

	  /*
      var title = 'An error occurred';
      var message = 'We were unable to get the information for this push message';  
      var icon = '/gcm/images/push-icon.png';  
      var notificationTag = 'notification-error';  
      return self.registration.showNotification(title, {  
          body: message,  
          icon: icon,  
          data: {
			data.utl: notificationTag  
		  }
        });
	  */
    })  
  );  
});

self.addEventListener('notificationclick', function(event) {
    console.log('Notification click: url ', event.notification.data.url);
    event.notification.close();
    var url = event.notification.data.url;
	if(url!==undefined){
		// Chek url is already opened then just focus on it else open this url in new window
		event.waitUntil(
			clients.matchAll({
				type: 'window'
			})
			.then(function(windowClients) {
				for (var i = 0; i < windowClients.length; i++) {
					var client = windowClients[i];
					if (client.url === url && 'focus' in client) {
						return client.focus();
					}
				}
				if (clients.openWindow) {
					return clients.openWindow(url);
				}
			})
		);
	}
});
