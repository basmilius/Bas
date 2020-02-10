<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

use Columba\Database\SQLiteDatabaseDriver;
use Columba\Foundation\Http\RateLimit\RateLimitMiddleware;
use Columba\Foundation\Http\RateLimit\Storage\IStorageAdapter;
use Columba\Foundation\Http\RateLimit\TimeWindowRateLimit;
use Columba\Http\RequestMethod;
use Columba\Router\Renderer\DebugRenderer;
use Columba\Router\Response\JsonResponse;
use Columba\Router\Router;

require_once __DIR__ . '/../bootstrap-test.php';

class RateLimitStorage implements IStorageAdapter
{

	private $db;

	public function __construct()
	{
		$this->db = new SQLiteDatabaseDriver(__DIR__ . '/rate-limit.sq3');
		/** @noinspection SqlNoDataSourceInspection */
		$this->db->exec('CREATE TABLE IF NOT EXISTS rate_limit (`key` TEXT PRIMARY KEY, `value` FLOAT, expire INTEGER)');
	}

	public function exists(string $key): bool
	{
		return $this->db
			->select('1')
			->from('rate_limit')
			->where(time() . ' < expire')
			->and('key', '=', [$key, PDO::PARAM_STR])
			->execute()
			->hasAtLeast(1);
	}

	public function get(string $key): float
	{
		return (float)$this->db
			->select('value')
			->from('rate_limit')
			->where(time() . ' < expire')
			->and('key', '=', [$key, PDO::PARAM_STR])
			->execute()
			->toSingle('value');
	}

	public function remove(string $key): void
	{
		$this->db
			->deleteFrom('rate_limit')
			->where('key', '=', [$key, PDO::PARAM_STR])
			->or('expire', '<', time())
			->execute();
	}

	public function set(string $key, float $value, int $ttl): void
	{
		$this->remove($key);

		$this->db
			->insertInto('rate_limit', 'key', 'value', 'expire')
			->values([$key, PDO::PARAM_STR], [$value, PDO::PARAM_STR], [time() + $ttl, PDO::PARAM_INT])
			->execute();
	}
}

$router = new Router(new JsonResponse(), new DebugRenderer());
$router->get('/', function (array $rateLimitStatus): array
{
	return $rateLimitStatus;
});
$router->use(RateLimitMiddleware::class, new TimeWindowRateLimit('test', 10, 10, new RateLimitStorage()));

$router->execute('/', RequestMethod::GET);
