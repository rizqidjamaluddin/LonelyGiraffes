<?php

/**
 * @method assertTrue($a)
 * @method assertFalse($a)
 * @method assertEquals($a, $b)
 * @method setExpectedException($exception)
 * @method markTestIncomplete($message = "")
 * @method markTestSkipped($message = "")
 */
abstract class TestCase extends Illuminate\Foundation\Testing\TestCase {

	/**
	 * Creates the application.
	 *
	 * @return \Symfony\Component\HttpKernel\HttpKernelInterface
	 */
	public function createApplication()
	{
		$unitTesting = true;

		$testEnvironment = 'testing';

		return require __DIR__.'/../../bootstrap/start.php';
	}

	public function be(\Illuminate\Auth\UserInterface $model, $driver = null)
	{
		$g = App::make('Giraffe\Authorization\Gatekeeper');
	    $g->iAm($model);
	    parent::be($model);
	}

	/**
	 * [as description]
	 * @param  [type] $model  [description]
	 * @param  [type] $driver [description]
	 * @return [type]         [description]
	 */
    public function asUser($model, $driver = null)
    {
	    $g = App::make('Giraffe\Authorization\Gatekeeper');

    	if (!$model instanceof \Illuminate\Auth\UserInterface) {
	    	$u = App::make('Giraffe\Users\UserRepository');
	    	$model = $u->getByHash($model);
	    }

	    $g->iAm($model);
	    parent::be($model);
    }

    public function disarm()
    {
        $gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
        $gatekeeper->disarm();
    }

}
