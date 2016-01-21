<?php

/**
 * This software package is licensed under AGPL or Commercial license.
 *
 * @package maslosoft/mangan
 * @licence AGPL or Commercial
 * @copyright Copyright (c) Piotr Masełkowski <pmaselkowski@gmail.com>
 * @copyright Copyright (c) Maslosoft
 * @copyright Copyright (c) Others as mentioned in code
 * @link http://maslosoft.com/mangan/
 */

namespace Maslosoft\ManganYii;

use CHttpSession;
use Maslosoft\Mangan\Criteria;
use Maslosoft\Mangan\EntityManager;
use Maslosoft\Mangan\Finder;
use Maslosoft\ManganYii\Models\Session;
use MongoDate;
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
 * @author Ianaré Sévi (merge into MongoDB)
 * @author aoyagikouhei (original author)
 */
class HttpSession extends CHttpSession
{

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
		$this->finder = Finder::create($this->model);
		$this->em = EntityManager::create($this->model);
	}

	/**
	 *
	 * @param string $id
	 * @return Session|null
	 */
	protected function getData($id)
	{
		$found = $this->finder->findByAttributes(['id' => $id]);
		if (null === $found)
		{
			return null;
		}
		$this->model = $found;
		return $found;
	}

	protected function getExipireTime()
	{
		return time() + $this->getTimeout();
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
	 * Session open handler.
	 * Do not call this method directly.
	 * @param string $savePath session save path
	 * @param string $sessionName session name
	 * @return boolean whether session is opened successfully
	 */
	public function openSession($savePath, $sessionName)
	{
		$this->gcSession(0);
	}

	/**
	 * Session read handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
		$document = $this->getData($id);
		return $document === null ? '' : $document->data;
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
		$this->model->data = $data;
		$this->model->ip = $_SERVER['REMOTE_ADDR'];
		$this->model->browser = $_SERVER[' HTTP_USER_AGENT'];
		$this->model->dateTime = new MongoDate();
		$this->model->userId = Yii::app()->user->id;
		$criteria = new Criteria(null, $this->model);
		$criteria->id = $id;
		return $this->em->updateOne($criteria, null, true);
	}

	/**
	 * Session destroy handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroySession($id)
	{
		$criteria = new Criteria(null, $this->model);
		$criteria->id = $id;
		return $this->em->deleteOne($criteria);
	}

	/**
	 * Session GC (garbage collection) handler.
	 * Do not call this method directly.
	 * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
	 * @return boolean whether session is GCed successfully
	 */
	public function gcSession($maxLifetime)
	{
		$criteria = new Criteria(null, $this->model);
		$criteria->addCond('expire', '$lt', time());
		$this->em->deleteAll($criteria);
	}

	/**
	 * Updates the current session id with a newly generated one.
	 * Please refer to {@link http://php.net/session_regenerate_id} for more details.
	 * @param boolean $deleteOldSession Whether to delete the old associated session file or not.
	 * @since 1.1.8
	 */
	public function regenerateID($deleteOldSession = false)
	{
		$oldId = session_id();

		parent::regenerateID(false);
		$newId = session_id();
		$document = $this->getData($oldId);
		if (is_null($document))
		{
			$model = new Session();
			$model->id = $newId;
			$this->em->insert($model);
		}
		elseif ($deleteOldSession)
		{
			$this->destroySession($document->id);
			$model = new Session();
			$model->id = $newId;
			$this->em->insert($model);
		}
		else
		{
			$this->model->id = $newId;
			$criteria = new Criteria(null, $this->model);
			$criteria->id = $oldId;
			$this->em->updateOne($criteria, ['id']);
		}
	}

}
