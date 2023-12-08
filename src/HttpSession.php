<?php

/**
 * This software package is licensed under AGPL, Commercial license.
 *
 * @package maslosoft/mangan-yii
 * @licence AGPL, Commercial
 * @copyright Copyright (c) Piotr Masełkowski <pmaselkowski@gmail.com>
 * @copyright Copyright (c) Maslosoft
 * @copyright Copyright (c) Others as mentioned in code
 * @link http://maslosoft.com/mangan-yii/
 */

namespace Maslosoft\ManganYii;

use CHttpSession;
use Exception;
use MongoDB\BSON\UTCDateTime;
use function function_exists;
use function headers_sent;
use Maslosoft\Mangan\Criteria;
use Maslosoft\Mangan\EntityManager;
use Maslosoft\Mangan\Finder;
use Maslosoft\Mangan\Interfaces\EntityManagerInterface;
use Maslosoft\Mangan\Mangan;
use Maslosoft\ManganYii\Models\Session;
use function php_sapi_name;
use Yii;

/**
 * HttpSession
 *
 * Example, in config/main.php:
 * ```php
 * 	'session' => [
 * 		'class' => \Maslosoft\ManganYii\HttpSession::class,
 * 	],
 * ```
 *
 * Optionally it can use custom connection id:
 *
 * ```php
 * 	'session' => [
 * 		'class' => \Maslosoft\ManganYii\HttpSession::class,
 * 			'connectionId' => 'manganConnectionId'
 * 	],
 * ```
 *
 * @author Ianaré Sévi (merge into MongoDB)
 * @author aoyagikouhei (original author)
 */
class HttpSession extends CHttpSession
{

	/**
	 * Optional connection id used to store state, if not set will use default
	 * @var string
	 */
	public $connectionId = Mangan::DefaultConnectionId;

	/**
	 * Finder instance
	 * @var Finder
	 */
	private $finder = null;

	/**
	 * Entity manager instance
	 * @var EntityManager
	 */
	private $em = null;
	private $mn = null;

	/**
	 *
	 * @var Session
	 */
	private $model = null;

	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init()
	{
		parent::init();
		$this->model = new Session();
		$this->mn = Mangan::fly($this->connectionId);

		$this->finder = Finder::create($this->model, $this->getEm(), $this->mn);

		if (isset($_SERVER['REMOTE_ADDR']))
		{
			if(empty($_SERVER['HTTP_USER_AGENT']))
			{
				$_SERVER['HTTP_USER_AGENT'] = 'unknown';
			}
			$this->model->ip = $_SERVER['REMOTE_ADDR'];
			if(function_exists('parse_user_agent'))
			{
				$ua = (object)parse_user_agent();
				$this->model->platform = $ua->platform;
				$this->model->browser = $ua->browser;
				$this->model->version = $ua->version;
			}
		}
		$this->model->dateTime = new UTCDateTime();
	}

	public function open(): void
	{
		if($this->canSetSession())
		{
			parent::open();
		}
	}

	/**
	 * Sets the session cookie parameters.
	 * The effect of this method only lasts for the duration of the script.
	 * Call this method before the session starts.
	 * @param array $value cookie parameters, valid keys include: lifetime, path,
	 * domain, secure, httponly. Note that httponly is all lowercase.
	 * @see http://us2.php.net/manual/en/function.session-set-cookie-params.php
	 */
	public function setCookieParams($value): void
	{
		if($this->canSetSession())
		{
			parent::setCookieParams($value);
		}
	}

	/**
	 * @param string $value the session name for the current session, must be an alphanumeric string, defaults to PHPSESSID
	 */
	public function setSessionName($value): void
	{
		if($this->canSetSession())
		{
			parent::setSessionName($value);
		}
	}


	/**
	 * Returns a value indicating whether to use custom session storage.
	 * This method overrides the parent implementation and always returns true.
	 * @return boolean whether to use custom storage.
	 */
	public function getUseCustomStorage()
	{
		return true;
	}

	/**
	 * Updates the current session id with a newly generated one.
	 * Please refer to {@link http://php.net/session_regenerate_id} for more details.
	 * @since 1.1.8
	 */
	public function regenerateID($deleteOldSession = false)
	{
		$oldId = session_id();

		// Session not started, ignore regenerate
		if (empty($oldId))
		{
			return;
		}

		if ($this->getIsStarted() && !headers_sent() && php_sapi_name() !== 'cli')
		{
			// Prevent php nightly warnings
			@session_regenerate_id(false);
		}
		$newId = session_id();

		// Something went wrong, do not save it
		if (empty($newId))
		{
			return;
		}
		$found = $this->finder->find($this->getCriteria($oldId));
		if ($found !== null)
		{
			$this->model = $found;
			$this->model->id = $newId;
			if ($deleteOldSession)
			{
				// Update old session id. NOTE: Variable name reads *delete*OldSession
				// because otherways we leave this session and insert new one
				$this->getEm()->updateOne($this->getCriteria($oldId), ['id']);
			}
			else
			{
				$this->getEm()->insert();
			}
		}
		else
		{
			$this->model->id = $newId;
			$this->getEm()->insert();
		}
	}

	/**
	 * Session read handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
		$found = $this->finder->find($this->getCriteria($id));
		if (null === $found || empty($found->id))
		{
			return '';
		}
		$this->model = $found;
		return $this->model->data;
	}

	/**
	 * Session write handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @param string $data session data
	 * @return boolean whether session write is successful
	 */
	public function writeSession($id, $data)
	{
		if (empty($id))
		{
			return false;
		}
		// exception must be caught in session write handler
		// http://us.php.net/manual/en/function.session-set-save-handler.php
		try
		{
			$this->model->id = $id;
			$this->model->data = $data;
			$this->model->expire = time() + $this->getTimeout();
			if (!empty(Yii::app()->user))
			{
				$this->model->userId = Yii::app()->user->id;
			}
			return $this->getEm()->updateOne($this->getCriteria($id));
		}
		catch (Exception $e)
		{

			echo $e->getMessage();
			// it is too late to log an error message here
			return false;
		}
	}

	/**
	 * Session destroy handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroySession($id): bool
	{
		return $this->em->deleteOne($this->getCriteria($id));
	}

	/**
	 * Session GC (garbage collection) handler.
	 * Do not call this method directly.
	 * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
	 * @return boolean whether session is GCed successfully
	 */
	public function gcSession($maxLifetime): bool
	{
		$criteria = new Criteria(null, $this->model);
		$criteria->addCond('expire', 'lt', time() - $maxLifetime);
		return $this->em->deleteAll($criteria);
	}

	/**
	 *
	 * @param string $id
	 * @return Criteria
	 */
	private function getCriteria($id): Criteria
	{
		$criteria = new Criteria(null, $this->model);
		$criteria->id = $id;
		return $criteria;
	}

	/**
	 * Get entity manager for update
	 * @return EntityManager|EntityManagerInterface
	 */
	private function getEm()
	{
		return $this->em = EntityManager::create($this->model, $this->mn);
	}

	private function canSetSession(): bool
	{
		return !headers_sent();
	}
}
