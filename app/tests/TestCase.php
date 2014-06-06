<?php

/**
 * @method assertTrue($a)
 * @method assertFalse($a)
 * @method assertEquals($a, $b)
 */
class TestCase extends Illuminate\Foundation\Testing\TestCase {

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



    public function disarm()
    {
        $gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
        $gatekeeper->disarm();
    }

}
