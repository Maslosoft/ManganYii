<?php

namespace Maslosoft\ManganYii\Models;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;

/**
 * @CollectionName('logs')
 */
class Log implements AnnotatedInterface
{
	public $message = '';

	public $level = '';

	public $category = '';

	public $timestamp = '';
}