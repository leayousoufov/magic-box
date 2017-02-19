<?php

namespace Fuzz\MagicBox\Middleware;


use Fuzz\MagicBox\Contracts\Repository;
use Fuzz\MagicBox\EloquentRepository;
use Fuzz\MagicBox\Utility\ModelResolver;
use Illuminate\Http\Request;


/**
 * The Repository Middleware uses the request to build an instance of the
 * EloquentRepository and bind it to the IoC. If a resource property is
 * set on the controller, or route it will also resolve the model.
 *
 * @example :
 *
 *         class PostsController extends Controller
 *         {
 *              public static $resource = Post::class;
 *         }
 *
 * You can type hint the Repository into the controller method and it will
 * automatically have the Brand model and request data set. So to use it
 * in a controller method all you have to do is:
 *
 *         public function show(Repository $repository, $id)
 *         {
 *              $post = $repository->read($id);
 *         }
 *
 * EloquentRepository will now read Post with the `$id` as well as apply
 * any filters and includes from the request.
 *
 * NOTE: When resolving the model, RepositoryMiddleware will follow order of
 * most specificity. That means resource properties on a route take precedence
 * over controller resource properties.
 *
 * @package Fuzz\MagicBox\Middleware
 */
class RepositoryMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle(Request $request, \Closure $next)
	{
		// Bind the repository contract to this concrete instance so it can be injected in resource routes
		app()->instance(Repository::class, $this->buildRepository($request));

		return $next($request);
	}

	/**
	 * Build a repository based on inbound request data.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Fuzz\MagicBox\EloquentRepository
	 */
	public function buildRepository(Request $request)
	{
		$input = [];

		/** @var \Illuminate\Routing\Route $route */
		$route = $request->route();


		// Resolve the model class if possible. And setup the repository.
		/** @var \Illuminate\Database\Eloquent\Model $model_class */
		$model_class = (new ModelResolver())->resolveModelClass($route);


		// If the method is not GET lets get the input from everywhere.
		// @TODO hmm, need to verify what happens on DELETE and PATCH.
		if ($request->method() !== 'GET') {
			$input += $request->all();
		}

		$repository = new EloquentRepository();

		// Instantiate an eloquent repository bound to our standardized route parameter
		$repository->setModelClass($model_class)
		           ->setFilters((array) $request->get('filters'))
		           ->setSortOrder((array) $request->get('sort'))
		           ->setGroupBy((array) $request->get('group'))
		           ->setEagerLoads((array) $request->get('include'))
		           ->setAggregate((array) $request->get('aggregate'))
		           ->setDepthRestriction(config('magicbox.default.eager_load_depth'))
		           ->setInput($input);

		return $repository;
	}
}