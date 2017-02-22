 <?php 


 define('APPLICATION_NAME', 'MasterSinge The Best');
        define('CREDENTIALS_PATH', __DIR__ . '/credentials/calendar-php-quickstart.json');
        define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
        define('SCOPES', implode(' ', array(
                \Google_Service_Calendar::CALENDAR)
        ));

        /**
         * Returns an authorized API client.
         * @return Google_Client the authorized client object
         */
        function getClient()
        {
            $client = new Google_Client();
            $client->setApplicationName(APPLICATION_NAME);
            $client->setScopes(SCOPES);
            $client->setAuthConfig(CLIENT_SECRET_PATH);
            $client->setAccessType('offline');

            // Load previously authorized credentials from a file.
            $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);


            if (file_exists($credentialsPath)) {
                $accessToken = json_decode(file_get_contents($credentialsPath), true);             

                if (isset($_GET['code'])){
                    $credentials = $client->authenticate($_GET['code']);
                    $code = trim($_GET['code']);
                    $authCode = $code;
                    $client->setAccessToken($accessToken);


                    // Refresh the token if it's expired.
                    if ($client->isAccessTokenExpired()) {
                        // save refresh token to some variable
                        $refreshTokenSaved = $client->getRefreshToken();

                        // update access token
                        $client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

                        // pass access token to some variable
                        $accessTokenUpdated = $client->getAccessToken();

                        // append refresh token
                        $accessTokenUpdated['refresh_token'] = $refreshTokenSaved;

                        // save to file
                        file_put_contents($credentialsPath, json_encode($accessTokenUpdated));
                    }
                        
                        $service = new Google_Service_Calendar($client);
                        $calendarList = $service->calendarList->listCalendarList();                
                       
                        $updatedEvent = $service->events->get('primary', '{{idevent}}');
                        $updatedEvent->setSummary('google api');
                        $updatedEvent = $service->events->update('primary', $updatedEvent->getId(), $updatedEvent);
                        echo $updatedEvent->getUpdated();

                        while (true) {
                            foreach ($calendarList->getItems() as $calendarListEntry) {
                                echo $calendarListEntry->getSummary() . "\n";
                                // get events
                                $events = $service->events->listEvents($calendarListEntry->id);

                                foreach ($events->getItems() as $event) {
                                    echo "<br/>" . $event->getSummary() . "";
                                    echo " ID : " . $event->getId() . "<br/>";
                                    echo "***********************" . "<br/> ";
                                }
                            }
                            $pageToken = $calendarList->getNextPageToken();

                            if ($pageToken) {
                                $optParams = array('pageToken' => $pageToken);
                                $calendarList = $service->calendarList->listCalendarList($optParams);
                            } else {
                                break;
                            }
                        }

                    }

            }else {

                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();

                printf("Open the following link in your browser:\n<a href='%s' > ici </a>\n",  $authUrl);
                print 'Enter verification code: ';

                /********TOKEN***********************/
                if(!empty($_GET['code'])) {
                    $code = trim($_GET['code']);
                    $authCode = $code;

                    // Exchange authorization code for an access token.
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

                    // Store the credentials to disk.
                    if (!file_exists(dirname($credentialsPath))) {
                        mkdir(dirname($credentialsPath), 0700, true);
                    }

                    $token = $client->getAccessToken();

                    if($client->isAccessTokenExpired()){  // if token expired
                      // refresh the token
                        $test=$client->refreshToken($token);
                    }
                    $accessToken = $client->getAccessToken();
                    file_put_contents($credentialsPath, json_encode($accessToken));
                    printf("Credentials saved to %s\n", $credentialsPath);
                    $client->authenticate($code);                  

                    if (isset($_GET['code'])) {
                        $client->authenticate($_GET['code']);
                        $_SESSION['token'] = $client->getAccessToken();
                        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
                    }

                    
                    if (isset($_SESSION['token'])) {
                        $client->setAccessToken($_SESSION['token']);
                        print "LogOut";
                        $service = new Google_Service_Calendar($client);
                        $calendarList = $service->calendarList->listCalendarList();

                        $googleApievent = new \Google_Service_Calendar_Event();
                        $googleApievent->setSummary('google api');
                        $googleApievent->setLocation('google api');

                        $start = new Google_Service_Calendar_EventDateTime();
                        $start->setDate('2017-02-16');
                        $start->setTimeZone('Europe/London');
                        $start->setDate('2017-02-16');

                        $end = new Google_Service_Calendar_EventDateTime();
                        $end->setDate('2017-02-17');
                        $end->setTimeZone('Europe/London');
                        $end->setDate('2017-02-17');

                        $googleApievent->setStart($start);
                        $googleApievent->setEnd($end);

                        $calendarId = "primary";

                        $createdEvent = $service->events->insert($calendarId, $googleApievent);
                        echo $createdEvent->getId();     


                        while (true) {
                            foreach ($calendarList->getItems() as $calendarListEntry) {
                                echo $calendarListEntry->getSummary() . "\n";
                                // get events
                                $events = $service->events->listEvents($calendarListEntry->id);

                                foreach ($events->getItems() as $event) {
                                    echo "<br/>" . $event->getSummary() . "";
                                    echo " ID : " . $event->getId() . "<br/>";
                                    echo "***********************" . "<br/> ";
                                }
                            }
                            $pageToken = $calendarList->getNextPageToken();

                            if ($pageToken) {
                                $optParams = array('pageToken' => $pageToken);
                                $calendarList = $service->calendarList->listCalendarList($optParams);
                            } else {
                                break;
                            }
                        }
                    }

                }
                return $client;
            }

        }
        function expandHomeDirectory($path)
            {
                $homeDirectory = getenv('HOME');
                if (empty($homeDirectory)) {
                    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
                }
                return str_replace('~', realpath($homeDirectory), $path);
            }


            // Get the API client and construct the service object.
            $client = getClient();
