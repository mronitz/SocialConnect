This plugin is to connect users to your CakePHP 2.x site using their Social Network account.

## Dependencies
* Cake/Utility/Xml.php
* Cake/Network/Http/HttpSocket.php

Both standard in Cake 2.x


## Installation

### Clone

Clone from github: in your plugin directory type 

	git clone https://github.com/mronitz/SocialConnect.git SocialConnect

### Submodule

Add as Git submodule: in your plugin directory type 

	git submodule add https://github.com/mronitz/SocialConnect.git SocialConnect

### Manual

Download as archive from github and extract to app/plugins/SocialConnect

### Loading

Load as Plugin with config as bootstrap in your `app/Config/bootstrap.php`:

	CakePlugin::load('SocialConnect',array('bootstrap' => array('config')));
	
Usable as a component
	
	public $components = array(
		'SocialConnect.SocialConnect'
	);

### Be sure to first get and set your social network keys (API keys/secret), and change the callback URL in the config file

## Example of usage

	// Get the accessToken, or redirect if it doesn't exists in the session yet.
			$accessToken = $this->SocialConnect->get_access_token(
				$networkName,
				array( // Callback url after connecting to network, login User usually
					'controller' => 'users',
					'action' => 'login',
					strtolower($networkName)
				)
			);

			// Fetch the profile data by giving the accessToken.
			$profileData = $this->SocialConnect->get_network_profile($networkName, $accessToken);
			pr($profileData);


## @TODO
*	Create easier config, perhaps even move the config to Cake itself instead of in the plugin
