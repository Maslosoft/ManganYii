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

namespace Maslosoft\ManganYii\Models;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Widgets\Grid\Column\TimeAgo;
use Maslosoft\Mangan\Sanitizers\DateSanitizer;
use Maslosoft\Mangan\Sanitizers\MongoObjectId;
use Maslosoft\Mangan\Sort;
use MongoId;

/**
 * Session
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Session implements AnnotatedInterface
{

	/**
	 * @PrimaryKey
	 * @Index(Sort::SortAsc)
	 * @Index(Sort::SortDesc)
	 *
	 * @see Sort
	 * @var string
	 */
	public $id = '';
	public $data = null;

	/**
	 * @Index
	 * @var int
	 */
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
