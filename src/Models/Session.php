<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\ManganYii\Models;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Ilmatar\Widgets\Grid\Column\TimeAgo;
use Maslosoft\Mangan\Sanitizers\DateSanitizer;
use Maslosoft\Mangan\Sanitizers\MongoObjectId;
use MongoId;

/**
 * Session
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Session implements AnnotatedInterface
{

	public $id = '';
	public $data = null;
	public $expire = 0;

	/**
	 * @Label('IP Address')
	 * @var string
	 */
	public $ip = '';

	/**
	 * @Label('Platform')
	 * @var string
	 */
	public $platform = '';

	/**
	 * @Label('Browser')
	 * @var string
	 */
	public $browser = '';

	/**
	 * @Label('Browser version')
	 * @var string
	 */
	public $version = '';

	/**
	 * @Label('Last activity')
	 * @Sanitizer(DateSanitizer)
	 * @Renderer(TimeAgo)
	 * @see TimeAgo
	 * @see DateSanitizer
	 * @var string
	 */
	public $dateTime = '';

	/**
	 * @Sanitizer(MongoObjectId, nullable = true)
	 * @see MongoObjectId
	 * @var MongoId
	 */
	public $userId = null;

}
