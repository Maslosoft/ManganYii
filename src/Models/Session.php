<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\ManganYii\Models;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Ilmatar\Widgets\Grid\Column\TimeAgo;
use Maslosoft\Mangan\Sanitizers\MongoObjectId;
use MongoId;

/**
 * Session
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Session implements AnnotatedInterface
{

	/**
	 * @Sanitizer(MongoObjectId)
	 * @see MongoObjectId
	 * @var MongoId
	 */
	public $_id = null;
	public $id = '';
	public $data = null;
	public $expire = 0;

	/**
	 * @Label('IP Address')
	 * @var string
	 */
	public $ip = '';

	/**
	 * @Label('Browser')
	 * @var string
	 */
	public $browser = '';

	/**
	 * @Label('Last activity')
	 * @Renderer(TimeAgo)
	 * @see TimeAgo
	 * @var string
	 */
	public $dateTime = '';

	/**
	 * @Sanitizer(MongoObjectId)
	 * @see MongoObjectId
	 * @var MongoId
	 */
	public $userId = null;

}
