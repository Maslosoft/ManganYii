<?php


namespace Tests\Unit;

use Codeception\Test\Unit;
use Maslosoft\Mangan\Finder;
use Maslosoft\Mangan\Mangan;
use Maslosoft\ManganYii\LogRoute;
use Maslosoft\ManganYii\Models\Log;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use Tests\Support\UnitTester;

class LogRouteTest extends Unit
{

    protected UnitTester $tester;

    protected function _before(): void
	{
		Mangan::fly()->getDbInstance()->dropCollection('logs');
    }

    // tests
    public function testSavingLogs(): void
    {
		$route = new LogRoute();
		$route->connectionID = 'mongodb';
		$route->init();

		$info = new ReflectionMethod($route, 'processLogs');
		$info->setAccessible(true);
		$log = [
			'Test message',
			'1',
			'test.testing',
			time(),
		];
		$info->invoke($route, [$log]);

		$finder = new Finder(new Log);
		$count = $finder->count();
		$this->assertSame(1, $count);
    }
}
