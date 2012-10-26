<?php
/**
 * Social Network Connect component for CakePHP 2.1
 *
 * @author Maikel Ronitz <maikel@noprotocol.nl>
 * @package dutch-creative
 * @subpackage Plugin
 *
 * Make sure to change the keys and secrets for each network in the Config/config.php
 *
 * Currently supports OAuth authenticated networks
 * - Twitter
 * - Linkedin
 * - Facebook
 *
 * # ------------------------------------------------------------------
 *
 * Load as Plugin with config as bootstrap:
 * 
 * CakePlugin::load('SocialConnect',array('bootstrap' => array('config')));
 *
 * 
 * Usable as Component:
 *
	public $components = array(
		'SocialConnect.SocialConnect'
	);
 *
 * $this->SocialConnect
 *
 * # ------------------------------------------------------------------
 *
 *
 * Dependencies:
 * - Cake/Utility/Xml.php
 * - Cake/Network/Http/HttpSocket.php
 */
class SocialConnectComponent extends Component
{
	/**
	 * The controller that initialized this component.
	 *
	 * @var Controller
	 */
	public $controller = null;

	/**
	 * The initialize method is called before the controller's beforeFilter method.
	 * @param Controller $controller
	 * @access public
	 * @return void
	 */
	public function initialize(Controller $controller)
	{
		$this->controller = $controller;
	}

	/**
	 * Get the access token for the chosen socialnetwork.
	 * The user will be redirected to the callbackRedirect url after the access
	 * token is fetched.
	 *
	 * @param string $networkName
	 * @param array $callbackRedirect
	 * @access public
	 * @return string
	 */
	public function get_access_token($networkName, $callbackRedirect = null)
	{
		// Format the network name and get the configuration for the given network.
		$networkName = ucfirst(strtolower($networkName));
		$networkConfig = Configure::read($networkName);

		// Get the access token
		$accessToken = $this->controller->Session->read($networkName . '.accessToken');

		// Redirect the user, if there is no access token in the session.
		if (empty($accessToken))
		{
			$this->controller->Session->delete($networkName . '.requestToken');
			$requestToken = $this->get_request_token($networkName, $callbackRedirect);
		} else
		{
			return json_decode($accessToken);
		}
	}

	/**
	 * Get the request token for the chosen socialnetwork.
	 * The user will be redirected to the callbackRedirect url after the access
	 * token is fetched.
	 *
	 * @param string $networkName
	 * @param array $callbackRedirect
	 * @access public
	 * @return string
	 */
	public function get_request_token($networkName, $callbackRedirect = null)
	{
		// Format the network name and get the configuration for the given network.
		$networkName = ucfirst(strtolower($networkName));
		$networkConfig = Configure::read($networkName);

		// Get the request token
		$requestToken = $this->controller->Session->read($networkName . '.requestToken');

		// Redirect the user, if there is no request token in the session.
		if (empty($requestToken))
		{
			$this->controller->Session->write($networkName . '.callbackRedirect', $callbackRedirect);

			$this->controller->redirect(
				array(
					'controller' => $this->controller->name,
					'action' => 'network_connect',
					strtolower($networkName)
				), 302, true
			);
		} else
		{
			return json_decode($requestToken);
		}
	}

	/**
	 * Request a request token.
	 * @access public
	 * @param string $networkName
	 * @return void
	 */
	public function network_connect($networkName)
	{
		// Format the network name and get the configuration for the given network.
		$networkName = ucfirst(strtolower($networkName));
		$networkConfig = Configure::read($networkName);

		// Get the configuration for the given network.
		$networkConfig = Configure::read(ucfirst(strtolower($networkName)));

		// Import the oAuth vendor.
		App::import('Vendor', 'SocialConnect.oauth', array('file' => 'OAuth' . DS . 'oauth_consumer.php'));
		$consumer = new OAuth_Consumer($networkConfig['consumerKey'], $networkConfig['consumerSecret']);

		// Build the callback url.
		$callbackUrl = is_array($networkConfig['callbackUrl']) ? Router::url($networkConfig['callbackUrl'], array('full' => true)) : $networkConfig['callbackUrl'];

		// Get and return the requestToken.
		if("facebook" == strtolower($networkName))
		{
			// facebook needs to authorize first..
			$url = sprintf($networkConfig['authorizeUrl'],$networkConfig['consumerKey'],$callbackUrl);

			// Why did the CallbackUrl get overwritten by the config? Lets just do that if it's set
			if(isset($networkConfig['callbackLoginUrl'])) {
				$this->controller->Session->write($networkName . '.callbackRedirect', $networkConfig['callbackLoginUrl']);
			}

			$this->controller->redirect($url);
		}
		else
		{
			$requestToken = $consumer->getRequestToken($networkConfig['requestTokenUrl'], $callbackUrl);
		}
		// requestToken[key] is the oauth_token

		// Store the request token in the session. Json encode is used to prevent an incomplete object error.
		$this->controller->Session->write($networkName . '.requestToken', json_encode($requestToken));

		// Redirect to the network authorization url.
		$this->controller->redirect(sprintf($networkConfig['authorizeUrl'], $requestToken->key));
	}

	/**
	 * Request a Social network access token.
	 * @access public
	 * @param string $networkName
	 * @return void
	 */
	public function network_callback($networkName)
	{
		// Format the network name and get the configuration for the given network.
		$networkName = ucfirst(strtolower($networkName));
		$networkConfig = Configure::read($networkName);

		// Get the request token
		if("facebook" != strtolower($networkName))
			$requestToken = $this->get_request_token($networkName);

		$accessToken = $this->controller->Session->read($networkName . '.accessToken');

		if (!empty($accessToken))
		{
			$accessToken = json_decode($accessToken);
		} 
		else
		{
			//pr('of toch hier');
			if("facebook" == strtolower($networkName))
			{
				// Import the oAuth vendor.
				App::import('Vendor', 'SocialConnect.oauth', array('file' => 'OAuth' . DS . 'oauth_consumer.php'));
				$consumer = new OAuth_Consumer($networkConfig['consumerKey'], $networkConfig['consumerSecret']);

				$code = $this->controller->request->query["code"];
				$callbackUrl = is_array($networkConfig['callbackUrl']) ? Router::url($networkConfig['callbackUrl'], array('full' => true)) : $networkConfig['callbackUrl'];
				
				$url = sprintf($networkConfig['requestTokenUrl'],$networkConfig['consumerKey'],$networkConfig['consumerSecret'],$callbackUrl,$code);

				$http = new HttpSocket();
				$response = $http->get($url);
				$token = $response->body;
				$token = str_replace("access_token=","",$token);
				$token = explode("&",$token);
				$accessToken = new OAuthToken($token[0], "secret");
			}
			else
			{
				// Get and decode the request token.
				$requestToken = $this->controller->Session->read($networkName . '.requestToken');
				$requestToken = json_decode($requestToken);

				// Get the configuration for the given network.
				$networkConfig = Configure::read(ucfirst(strtolower($networkName)));

				// Import the oAuth vendor.
				App::import('Vendor', 'SocialConnect.oauth', array('file' => 'OAuth' . DS . 'oauth_consumer.php'));
				$consumer = new OAuth_Consumer($networkConfig['consumerKey'], $networkConfig['consumerSecret']);
				$accessToken = $consumer->getAccessToken($networkConfig['accessTokenUrl'], $requestToken);
			}

			if(false === $accessToken) // pressed cancel?
				$this->controller->redirect('/');

			// Store the request token in the session. Json encode is used to prevent an incomplete object error.
			$this->controller->Session->write($networkName . '.accessToken', json_encode($accessToken));
		}

		$callbackRedirect = $this->controller->Session->read($networkName . '.callbackRedirect');
		$this->controller->Session->delete($networkName . '.callbackRedirect');

		if(null !== $callbackRedirect && false !== $callbackRedirect)
			$this->controller->redirect($callbackRedirect, 302, true);
	}

	/**
	 * The startup method is called after the controller's beforeFilter method but before the controller executes the current action handler.
	 *
	 * @param Controller $controller
	 * @access public
	 * @return void
	 */
	public function startup(Controller $controller)
	{
		if ($this->controller->action == 'network_connect')
		{
			$this->network_connect($this->controller->params['pass'][0]);
		} elseif ($this->controller->action == 'network_callback')
		{
			$this->network_callback($this->controller->params['pass'][0]);
		}
	}

	/**
	 * Gets the profile data for the selected socialnetwork.
	 *
	 * @param string $networkName
	 * @param string $accessToken
	 * @access public
	 * @return array
	 *
	 * @todo Move to SocialNetworkComponent?
	 */
	public function get_network_profile($networkName, $accessToken, $raw = false)
	{
		// Get the configuration for the given network.
		$networkConfig = Configure::read(ucfirst(strtolower($networkName)));

		App::import('Vendor', 'SocialConnect.oauth', array('file' => 'OAuth' . DS . 'oauth_consumer.php'));
		$consumer = new OAuth_Consumer($networkConfig['consumerKey'], $networkConfig['consumerSecret']);
		$result = $consumer->get($accessToken->key, $accessToken->secret, $networkConfig['getProfileUrl']);

		App::import('Utility', 'Xml');

		if("facebook" == strtolower($networkName))
		{
			$profileData = json_decode($result['body'],true);
		}
		else
		{
			// Imported Xml class has a magic function _toArray(), so we can typecast a SimpleXML object to an array.
			$xml = Xml::build($result['body']);
			$profileData = (array) $xml;
		}

		foreach($profileData as $field => $data)
		{
			if(!is_array($data)) $profileData[$field] = utf8_decode($data);
		}

		if ($raw)
			return $profileData;

		$error = false;
		if (isset($profileData['error']))
		{
			$error = true;
		}

		// Set the fields.
		if (strtolower($networkName) == 'twitter')
		{
			if ($error)
				return array('Error' => 1);

			return array(
				'Network' => array(
					'user_id' => $profileData['id'],
					'user_name' => utf8_encode($profileData['screen_name']),
					'name' => $profileData['name'],
					'picture_url' => $profileData['profile_image_url'],
					'user_network_url' => 'http://www.twitter.com/' . $profileData['screen_name']
				)
			);
		}
		else if (strtolower($networkName) == 'linkedin')
		{
			if ($error)
				return array('Error' => 1);

			return array(
				'Network' => array(
					'user_id' => $profileData['id'],
					'user_name' => utf8_encode($profileData['first-name'] . ' ' . $profileData['last-name']),
					'name' => $profileData['first-name'] . ' ' . $profileData['last-name'],
					'picture_url' => isset($profileData['picture-url']) ? $profileData['picture-url'] : "",
					'user_network_url' => $profileData['public-profile-url']
				)
			);
		}
		else if (strtolower($networkName) == 'facebook')
		{
			if ($error)
				return array('Error' => 1);

			return array(
				'Network' => array(
					'user_id' => $profileData['id'],
					'user_name' => $profileData['username'],
					'name' => utf8_encode($profileData['name']),
					'picture_url' => '',
					'user_network_url' => $profileData['link']
				)
			);
		}
	}

	/**
	 * social Media Logout
	 * Destroys the session connection to the network
	 *
	 * @author Maikel <maikel@noprotocol.nl>
	 */
	public function socialMediaLogout($networkName)
	{
		$networkName = ucfirst(strtolower($networkName));
		$this->controller->Session->delete($networkName . '.accessToken');
	}

}

?>