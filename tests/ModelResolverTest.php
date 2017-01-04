<?php

namespace Fuzz\MagicBox\Tests;


use Fuzz\MagicBox\Tests\Models\Post;
use Fuzz\MagicBox\Tests\Models\User;
use Fuzz\MagicBox\Utility\ModelResolver;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Route;
use Mockery;


class ModelResolverTest extends TestCase
{

	/**
	 * Given a route has a property named resource
	 * When the model is resolved
	 * Then the resolver should return that properties value.
	 *
	 * Example:
	 *      $router->get('/users', ['resource' => User::class, 'uses' => 'UserController@getUsers']);
	 */
	public function testCanResolveModelClassOnRoute()
	{
		$route = Mockery::mock(Route::class);
		$route->shouldReceive('getAction')->twice()->andReturn([
			'resource' => User::class
		]);

		$model = (new ModelResolver())->resolveModelClass($route);

		$this->assertEquals(User::class, $model);
		$this->assertNotEquals(Post::class, $model);
	}

	/**
	 * Given a controller has a static property named $resource
	 * When the model is resolved
	 * Then the resolver should return the properties value.
	 *
	 * Example:
	 *      $router->get('/users', ['uses' => 'UserController@getUsers']);
	 *
	 *      class UserController extends BaseController {
	 *          ...
	 *
	 *          public static $resource = User::class;
	 *
	 *          ...
	 *      }
	 */
	public function testCanResolveModelClassOnController()
	{
		$route = Mockery::mock(Route::class);
		$route->shouldReceive('getAction')->zeroOrMoreTimes()->andReturn([
			'uses' => 'Fuzz\MagicBox\Tests\UserController@getUsers'
		]);

		$model = (new ModelResolver())->resolveModelClass($route);

		$this->assertEquals(User::class, $model);
		$this->assertNotEquals(Post::class, $model);
	}

	/**
	 * Given the route is a closure
	 * When the model is resolved
	 * Then the resolver should return false because it cannot be resolved.
	 *
	 * Example:
	 *      $router->get('/users', function($request) {
	 *          return response()->json([], 200);
	 *      })
	 */
	public function testCanResolveModelClassToFalse()
	{
		$route = Mockery::mock(Route::class);
		$route->shouldReceive('getAction')->zeroOrMoreTimes()->andReturn([
			'uses' => function() {
				return null;
			}
		]);

		$model = (new ModelResolver())->resolveModelClass($route);

		$this->assertFalse($model);
		$this->assertNotEquals(Post::class, $model);
	}
}


/**
 * Class UserController
 *
 * @package Fuzz\MagicBox\Tests
 */
class UserController extends Controller
{
	public static $resource = User::class;
}