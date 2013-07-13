<?php

/**
 * @package     Extly.FacebookApp
 * @subpackage  ServerAuth - AutoTweetNG posts content to social channels (Twitter, Facebook, LinkedIn, etc).
 *
 * @author      Prieco S.A. <support@extly.com>
 * @copyright   Copyright (C) 2007 - 2012 Prieco, S.A. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        http://www.extly.com http://support.extly.com
 */

// No direct access
defined('_JEXEC') or die('Restricted index access');

// FacebookApp to authorize and grant permissions for the AutoTweet standard App or your own App

/**
 * FacebookApp class.
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class FacebookApp
{

	public $APP_ID = null;

	public $APP_SECRET = null;

	public $CANVAS_PAGE = null;

	public $permissions = 'publish_stream,offline_access,manage_pages,user_events,user_groups,user_photos,user_videos,create_event,photo_upload,video_upload';

	public $login_url = null;

	public $facebook = null;

	/**
	 * Constructor.
	 *
	 * @since	1.0
	 */
	public function __construct()
	{

	}

	/**
	 * Init from request.
	 *
	 * @return	void
	 *
	 * @since	1.0
	 */
	public function init()
	{
		// First step - to be authorized
		if (!isset($_REQUEST['state']))
		{
			// Called form AutoTweet backend
			$this->APP_ID = filter_input(INPUT_GET, 'app_id', FILTER_SANITIZE_STRING);
			$this->APP_SECRET = filter_input(INPUT_GET, 'secret', FILTER_SANITIZE_STRING);
			$this->CANVAS_PAGE = filter_input(INPUT_GET, 'canvas_page', FILTER_SANITIZE_URL);
		}
		else
		{
			// Second Step - Authorized
			$state = filter_input(INPUT_GET, 'state', FILTER_SANITIZE_STRING);
			$state = explode(',', $state);

			if (count($state) == 3)
			{
				$this->APP_ID = $state[0];
				$this->APP_SECRET = $state[1];
				$this->CANVAS_PAGE = $state[2];
			}
		}

		if (((empty($this->APP_ID)) || ($this->APP_ID === 'My-App-ID')) && (defined('MY_APP_ID')))
		{
			$this->APP_ID = MY_APP_ID;
		}

		if (((empty($this->APP_SECRET)) || ($this->APP_SECRET === 'My-App-Secret')) && (defined('MY_APP_SECRET')))
		{
			$this->APP_SECRET = MY_APP_SECRET;
		}

		if ((empty($this->CANVAS_PAGE)) && (defined('MY_CANVAS_PAGE')))
		{
			$this->CANVAS_PAGE = MY_CANVAS_PAGE;
		}

		if (DEBUG_ENABLED)
		{
			echo '<div class="alert alert-block alert-error"><button data-dismiss="alert" class="close" type="button">×</button>';
			echo '<h2>Debug information part 1: request data</h2>';
			echo '<p>request: ';
			print_r($_REQUEST);
			echo '</p>';
			echo '<ul>';
			echo '<li>app id: ' . $this->APP_ID . '</li>';
			echo '<li>api secret: ' . $this->APP_SECRET . '</li>';
			echo '<li>canvas page: ' . $this->CANVAS_PAGE . '</li>';
			echo '</ul>';
			echo '</div>';
		}

		$ok = !(empty($this->APP_ID) || empty($this->APP_SECRET) || empty($this->CANVAS_PAGE));
		if ($ok)
		{
			$this->facebook = new facebookphpsdk\Facebook(array('appId' => $this->APP_ID, 'secret' => $this->APP_SECRET, 'cookie' => true,));
		}

		return $ok;
	}

	/**
	 * Login.
	 *
	 * @return	void
	 *
	 * @since	1.0
	 */
	public function login()
	{
		$user = $this->facebook->getUser();

		if (empty($user) || !isset($_REQUEST['state']))
		{
			$ref = $this->CANVAS_PAGE;
			if (empty($ref))
			{
				if (defined('MY_CANVAS_PAGE'))
				{
					$ref = MY_CANVAS_PAGE;
				}
				else
				{
					$ref = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL);
				}
			}

			if (empty($ref))
			{
				echo '<div class="alert alert-block alert-error"><button data-dismiss="alert" class="close" type="button">×</button>';
				echo '<h2>HTTP_REFERER not available.</h2>';
				echo '<ul>';
				echo '<li>Please, enable the HTTP_REFERER or define MY_CANVAS_PAGE.</li>';
				echo '</ul>';
				echo '</div>';
			}

			$params = array(
				'redirect_uri' => $ref,
				'scope' => $this->permissions,
				'state' => $this->APP_ID . ',' . $this->APP_SECRET . ',' . $ref,
			);
			$this->login_url = $this->facebook->getLoginUrl($params);

			if (DEBUG_ENABLED)
			{
				echo '<div class="alert alert-block alert-error"><button data-dismiss="alert" class="close" type="button">×</button>';
				echo '<h2>Debug information part 2: login data</h2>';
				echo '<ul>';
				echo '<li>user: ' . print_r($user, true) . '</li>';
				echo '<li>state: ' . $_REQUEST['state'] . '</li>';
				echo '<li>login_url: ' . $this->login_url . '</li>';
				echo '</ul>';
				echo '</div>';
			}
			return false;
		}
		return true;
	}

}
