<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\ManganYii;

use Maslosoft\Mangan\Criteria;
use Maslosoft\Mangan\EntityManager;
use Maslosoft\Mangan\Finder;
use Maslosoft\Mangan\Mangan;
use Maslosoft\ManganYii\Interfaces\StatePersisterInterface;
use Maslosoft\ManganYii\Models\State;

/**
 * StatePersister
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class StatePersister implements StatePersisterInterface
{

	/**
	 * Optional connection id used to store state, if empty will use default
	 * @var string
	 */
	public $connectionId = '';

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
	 * @var State
	 */
	private $model = null;

	public function init()
	{
		$this->model = new State;
		$mangan = Mangan::fly($this->connectionId);
		$this->em = EntityManager::create($this->model, $mangan);
		$this->finder = Finder::create($this->model, $this->em, $mangan);
	}

	public function load()
	{
		$found = $this->finder->findByAttributes(['stateId' => __CLASS__]);
		if (null === $found)
		{
			return null;
		}
		$this->model = $found;
		return json_decode($found->data, true);
	}

	public function save($state)
	{
		$this->model->data = json_encode($state);
		$this->model->stateId = __CLASS__;
		$criteria = new Criteria();
		$criteria->stateId = __CLASS__;
		return $this->em->updateOne($criteria, null, true);
	}

}
