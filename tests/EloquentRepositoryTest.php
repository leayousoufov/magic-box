<?php

namespace Fuzz\MagicBox\Tests;

use Illuminate\Support\Facades\DB;
use Fuzz\MagicBox\Tests\Models\User;
use Fuzz\MagicBox\Tests\Models\Post;
use Fuzz\MagicBox\EloquentRepository;
use Fuzz\MagicBox\Tests\Models\Profile;
use Illuminate\Database\Eloquent\Builder;

class EloquentRepositoryTest extends DBTestCase
{
	/**
	 * Retrieve a sample repository for testing.
	 *
	 * @param string|null $model_class
	 * @param array       $input
	 * @return \Fuzz\MagicBox\EloquentRepository|static
	 */
	private function getRepository($model_class = null, array $input = [])
	{
		if (! is_null($model_class)) {
			return (new EloquentRepository)->setModelClass($model_class)->setInput($input);
		}

		return new EloquentRepository;
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testItRejectsUnfuzzyModels()
	{
		$repo = (new EloquentRepository)->setModelClass('NotVeryFuzzy');
	}

	public function testItCanCreateASimpleModel()
	{
		$user = $this->getRepository('Fuzz\MagicBox\Tests\Models\User')->save();
		$this->assertNotNull($user);
		$this->assertEquals($user->id, 1);
	}

	public function testItCanFindASimpleModel()
	{
		$repo       = $this->getRepository('Fuzz\MagicBox\Tests\Models\User');
		$user       = $repo->save();
		$found_user = $repo->find($user->id);
		$this->assertNotNull($found_user);
		$this->assertEquals($user->id, $found_user->id);
	}

	public function testItCountsCollections()
	{
		$repository = $this->getRepository('Fuzz\MagicBox\Tests\Models\User');
		$this->assertEquals($repository->count(), 0);
		$this->assertFalse($repository->hasAny());
	}


	public function testItCanGroupByFields()
	{
		$repository     = $this->getRepository('Fuzz\MagicBox\Tests\Models\User');
		$NY_user_one    = $repository->setInput(['username' => 'bob', 'points' => 100, 'state' => 'NY'])->save();
		$NY_user_two    = $repository->setInput(['username' => 'joe', 'points' => 400, 'state' => 'NY'])->save();
		$NY_user_three  = $repository->setInput(['username' => 'sam', 'points' => 300, 'state' => 'NY'])->save();
		$KY_user_one    = $repository->setInput(['username' => 'will', 'points' => 500, 'state' => 'KY'])->save();
		$KY_user_two    = $repository->setInput(['username' => 'john', 'points' => 20, 'state' => 'KY'])->save();
		$CA_user_one    = $repository->setInput(['username' => 'sue', 'points' => 342, 'state' => 'CA'])->save();

		$ungrouped_users = $repository->all();
		$this->assertEquals($ungrouped_users->count(), 6);
		$this->assertEquals($ungrouped_users->where('state', 'NY')->count(), 3);
		$this->assertEquals($ungrouped_users->where('state', 'KY')->count(), 2);
		$this->assertEquals($ungrouped_users->where('state', 'CA')->count(), 1);

		$grouped_users  = $repository->setGroupBy(['state'])->all();
		$this->assertEquals($grouped_users->count(), 3);
		$this->assertEquals($grouped_users->where('state', 'NY')->count(), 1);
		$this->assertEquals($grouped_users->where('state', 'KY')->count(), 1);
		$this->assertEquals($grouped_users->where('state', 'CA')->count(), 1);
	}

	public function testItCanFilterOnFields()
	{
		$repository  = $this->getRepository('Fuzz\MagicBox\Tests\Models\User');
		$first_user  = $repository->setInput(['username' => 'bob'])->save();
		$second_user = $repository->setInput(['username' => 'sue'])->save();
		$this->assertEquals($repository->all()->count(), 2);

		$found_users = $repository->setFilters(['username' => '=sue'])->all();
		$this->assertEquals($found_users->count(), 1);
		$this->assertEquals($found_users->first()->username, 'sue');
	}

	public function testItPaginates()
	{
		$repository  = $this->getRepository('Fuzz\MagicBox\Tests\Models\User');
		$first_user  = $repository->setInput(['username' => 'bob'])->save();
		$second_user = $repository->setInput(['username' => 'sue'])->save();

		$paginator = $repository->paginate(1);
		$this->assertInstanceOf('Illuminate\Pagination\LengthAwarePaginator', $paginator);
		$this->assertTrue($paginator->hasMorePages());
	}

	public function testItEagerLoadsRelationsSafely()
	{
		$this->getRepository(
			'Fuzz\MagicBox\Tests\Models\User', [
				'username' => 'joe',
				'posts'    => [
					[
						'title' => 'Some Great Post',
					],
				]
			]
		)->save();

		$user = $this->getRepository('Fuzz\MagicBox\Tests\Models\User')->setFilters(['username' => 'joe'])
			->setEagerLoads(
				[
					'posts.nothing',
					'nada'
				]
			)->all()->first();

		$this->assertNotNull($user);
		$this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->posts);
		$this->assertInstanceOf('Fuzz\MagicBox\Tests\Models\Post', $user->posts->first());
	}

	public function testItCanFillModelFields()
	{
		$user = $this->getRepository('Fuzz\MagicBox\Tests\Models\User', ['username' => 'bob'])->save();
		$this->assertNotNull($user);
		$this->assertEquals($user->username, 'bob');
	}

	public function testItUpdatesExistingModels()
	{
		$user = $this->getRepository('Fuzz\MagicBox\Tests\Models\User', ['username' => 'bobby'])->save();
		$this->assertEquals($user->id, 1);
		$this->assertEquals($user->username, 'bobby');

		$user = $this->getRepository(
			'Fuzz\MagicBox\Tests\Models\User', [
				'id'       => 1,
				'username' => 'sue'
			]
		)->save();
		$this->assertEquals($user->id, 1);
		$this->assertEquals($user->username, 'sue');
	}

	public function testItDeletesModels()
	{
		$user = $this->getRepository('Fuzz\MagicBox\Tests\Models\User', ['username' => 'spammer'])->save();
		$this->assertEquals($user->id, 1);
		$this->assertTrue($user->exists());

		$this->getRepository('Fuzz\MagicBox\Tests\Models\User', ['id' => 1])->delete();
		$this->assertNull(User::find(1));
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testItExpectsInputIds()
	{
		$this->getRepository('Fuzz\MagicBox\Tests\Models\User', ['username' => 'joe'])->getInputId();
	}

	public function testItFillsBelongsToRelations()
	{
		$post = $this->getRepository(
			'Fuzz\MagicBox\Tests\Models\Post', [
				'title' => 'Some Great Post',
				'user'  => [
					'username' => 'jimmy',
				],
			]
		)->save();

		$this->assertNotNull($post->user);
		$this->assertEquals($post->user->username, 'jimmy');
	}

	public function testItFillsHasManyRelations()
	{
		$user = $this->getRepository(
			'Fuzz\MagicBox\Tests\Models\User', [
				'username' => 'joe',
				'posts'    => [
					[
						'title' => 'Some Great Post',
					],
					[
						'title' => 'Yet Another Great Post',
					],
				]
			]
		)->save();

		$this->assertEquals(
			$user->posts->lists('id')->toArray(), [
				1,
				2
			]
		);

		$post = Post::find(2);
		$this->assertNotNull($post);
		$this->assertEquals($post->user_id, $user->id);
		$this->assertEquals($post->title, 'Yet Another Great Post');

		$this->getRepository(
			'Fuzz\MagicBox\Tests\Models\User', [
				'id'    => $user->id,
				'posts' => [
					[
						'id' => 1,
					],
				],
			]
		)->save();

		$user->load('posts');

		$this->assertEquals(
			$user->posts->lists('id')->toArray(), [
				1,
			]
		);

		$post = Post::find(2);
		$this->assertNull($post);
	}

	public function testItFillsHasOneRelations()
	{
		$user = $this->getRepository(
			'Fuzz\MagicBox\Tests\Models\User', [
				'username' => 'joe',
				'profile'  => [
					'favorite_cheese' => 'brie',
				],
			]
		)->save();

		$this->assertNotNull($user->profile);
		$this->assertEquals($user->profile->favorite_cheese, 'brie');
		$old_profile_id = $user->profile->id;

		$user = $this->getRepository(
			'Fuzz\MagicBox\Tests\Models\User', [
				'id'      => $user->id,
				'profile' => [
					'favorite_cheese' => 'pepper jack',
				],
			]
		)->save();

		$this->assertNotNull($user->profile);
		$this->assertEquals($user->profile->favorite_cheese, 'pepper jack');

		$this->assertNotEquals($user->profile->id, $old_profile_id);
		$this->assertNull(Profile::find($old_profile_id));
	}

	public function testItCascadesThroughSupportedRelations()
	{
		$post = $this->getRepository(
			'Fuzz\MagicBox\Tests\Models\Post', [
				'title' => 'All the Tags',
				'user'  => [
					'username' => 'simon',
					'profile'  => [
						'favorite_cheese' => 'brie',
					],
				],
				'tags'  => [
					[
						'label' => 'Important Stuff',
					],
					[
						'label' => 'Less Important Stuff',
					],
				],
			]
		)->save();

		$this->assertEquals($post->tags()->count(), 2);
		$this->assertNotNull($post->user->profile);
		$this->assertNotNull($post->user->profile->favorite_cheese, 'brie');
	}

	public function testItUpdatesBelongsToManyPivots()
	{
		$post = $this->getRepository(
			'Fuzz\MagicBox\Tests\Models\Post', [
				'title' => 'All the Tags',
				'user'  => [
					'username' => 'josh',
				],
				'tags'  => [
					[
						'label' => 'Has Extra',
						'pivot' => [
							'extra' => 'Meowth'
						],
					],
				],
			]
		)->save();

		$tag = $post->tags->first();
		$this->assertEquals($tag->pivot->extra, 'Meowth');

		$post = $this->getRepository(
			'Fuzz\MagicBox\Tests\Models\Post', [
				'id'   => $post->id,
				'tags' => [
					[
						'id'    => $tag->id,
						'pivot' => [
							'extra' => 'Pikachu',
						],
					],
				],
			]
		)->save();

		$tag = $post->tags->first();
		$this->assertEquals($tag->pivot->extra, 'Pikachu');
	}

	public function testItSorts()
	{
		$repository  = $this->getRepository('Fuzz\MagicBox\Tests\Models\User');
		$first_user  = $repository->setInput(
			[
				'username' => 'Bobby'
			]
		)->save();
		$second_user = $repository->setInput(
			[
				'username' => 'Robby'
			]
		)->save();
		$this->assertEquals($repository->all()->count(), 2);

		$found_users = $repository->setSortOrder(
			[
				'id' => 'desc'
			]
		)->all();
		$this->assertEquals($found_users->count(), 2);
		$this->assertEquals($found_users->first()->id, 2);
	}

	public function testItModifiesQueries()
	{
		$repository = $this->getRepository('Fuzz\MagicBox\Tests\Models\User', ['username' => 'Billy']);
		$repository->save();
		$this->assertEquals($repository->count(), 1);
		$repository->setModifiers([
			function(Builder $query) {
				$query->whereRaw(DB::raw('0 = 1'));
			}
		]);
		$this->assertEquals($repository->count(), 0);
	}
}
