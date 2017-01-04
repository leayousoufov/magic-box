<?php

namespace Fuzz\MagicBox\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Post extends Model
{
	/**
	 * @var string
	 */
	protected $table = 'posts';

	/**
	 * @var array
	 */
	protected $fillable = ['title', 'user_id', 'user', 'tags'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo('Fuzz\MagicBox\Tests\Models\User');
	}

	/**
	 * @return $this
	 */
	public function tags(): BelongsToMany
	{
		return $this->belongsToMany('Fuzz\MagicBox\Tests\Models\Tag')->withPivot('extra');
	}
}
