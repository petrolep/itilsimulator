<?php
namespace ITILSimulator\Base;

use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory to create routing rules for the application.
 */
class RouterFactory
{
	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
        //$router[] = new \Nette\Application\Routers\CliRouter(array('action' => 'DoctrineTest:cli'));
		$router[] = new Route('index.php', 'Trainer:Default:default', Route::ONE_WAY);

		$router[] = $adminRouter = new RouteList('Creator');
		$adminRouter[] = new Route('[<lang (en|cz)>/]creator/<presenter>/<action>', 'Default:default');

		$router[] = $frontRouter = new RouteList('Trainer');
		$frontRouter[] = new Route('[<lang (en|cz)>/]<trainingStepId [0-9]+>/<presenter>/<action>[/<id>]', 'Training:default');

		//$router[] = $frontRouter = new RouteList('Trainer');
		$frontRouter[] = new Route('[<lang (en|cz)>/]info/<action>[/<id>]', array('presenter' => 'default'));


		//$router[] = $frontRouter = new RouteList('Trainer');
		$frontRouter[] = new Route('[<lang (en|cz)>/]<presenter>/<action>[/<id>]', 'Default:default');

		//$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}
