<?php
/*
 * LinkedIn
 */
#	Configurable Settings for LinkedIn
Configure::write('Linkedin.consumerKey', '##### KEY #####');
Configure::write('Linkedin.consumerSecret', '##### SECRET #####');

#	API Call Settings for LinkedIn; Do not touch unless the API changes
Configure::write('Linkedin.requestTokenUrl', 'https://api.linkedin.com/uas/oauth/requestToken');
Configure::write('Linkedin.authorizeUrl', 'https://www.linkedin.com/uas/oauth/authenticate?oauth_token=%s');
Configure::write('Linkedin.callbackUrl', array('controller' => 'users', 'action' => 'network_callback', 'linkedin'));
Configure::write('Linkedin.accessTokenUrl', 'https://api.linkedin.com/uas/oauth/accessToken');
Configure::write('Linkedin.getProfileUrl', 'http://api.linkedin.com/v1/people/~:(id,first-name,last-name,headline,industry,summary,specialties,interests,phone-numbers,im-accounts,twitter-accounts,date-of-birth,main-address,picture-url,public-profile-url)');

# ------------------------------------------------------------------

/**
 * Twitter
 */
#	Configurable Settings for Twitter
Configure::write('Twitter.consumerKey', '##### KEY #####');
Configure::write('Twitter.consumerSecret', '##### SECRET #####');

#	API Call Settings for Twitter; Do not touch unless the API changes
Configure::write('Twitter.requestTokenUrl', 'https://api.twitter.com/oauth/request_token');
Configure::write('Twitter.authorizeUrl', 'https://api.twitter.com/oauth/authenticate?oauth_token=%s');
Configure::write('Twitter.callbackUrl', array('controller' => 'users', 'action' => 'network_callback', 'twitter'));
Configure::write('Twitter.accessTokenUrl', 'https://api.twitter.com/oauth/access_token');
Configure::write('Twitter.getProfileUrl', 'http://api.twitter.com/1/account/verify_credentials.xml');

# ------------------------------------------------------------------

/**
 * Facebook
 */
#	Configurable Settings for Facebook
Configure::write('Facebook.consumerKey', '##### KEY #####');
Configure::write('Facebook.consumerSecret', '##### SECRET #####');

#	API Call Settings for Facebook; Do not touch unless the API changes
Configure::write('Facebook.requestTokenUrl', 'https://graph.facebook.com/oauth/access_token?client_id=%s&client_secret=%s&redirect_uri=%s&code=%s');
Configure::write('Facebook.authorizeUrl', 'https://graph.facebook.com/oauth/authorize?client_id=%s&redirect_uri=%s');
Configure::write('Facebook.callbackUrl', array('controller' => 'users', 'action' => 'network_callback', 'facebook'));
Configure::write('Facebook.callbackLoginUrl', array('controller' => 'users', 'action' => 'login', 'facebook'));
Configure::write('Facebook.getProfileUrl', 'https://graph.facebook.com/me?%s');
?>