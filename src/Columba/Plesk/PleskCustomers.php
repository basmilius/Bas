<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Plesk;

use Columba\Util\ArrayUtil;
use Exception;

/**
 * Class PleskCustomers
 *
 * @package Columba\Plesk
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
final class PleskCustomers extends PleskSubSystem
{

	/**
	 * Creates a new customer.
	 *
	 * @param string $name
	 * @param string $contactName
	 * @param string $login
	 * @param string $password
	 * @param string $phonenumber
	 * @param string $email
	 * @param string $address
	 * @param string $city
	 * @param string $state
	 * @param string $postalCode
	 * @param string $country
	 *
	 * @return array|null
	 * @throws PleskException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function createCustomer(string $name, string $contactName, string $login, string $password, string $phonenumber, string $email, string $address, string $city, string $state, string $postalCode, string $country): ?array
	{
		try
		{
			$customer = $this->client->request([
				'customer' => [
					'add' => [
						'gen_info' => [
							'cname' => $name,
							'pname' => $contactName,
							'login' => $login,
							'passwd' => $password,
							'status' => 0,
							'phone' => $phonenumber,
							'email' => $email,
							'address' => $address,
							'city' => $city,
							'state' => $state,
							'pcode' => $postalCode,
							'country' => $country
						]
					]
				]
			]);

			$customer = PleskApiUtil::flatten($customer, 'customer', 'add', 'result');

			return [
				'id' => $customer['id'],
				'guid' => $customer['guid']
			];
		}
		catch (Exception $err)
		{
			throw new PleskException('Could not create customer: ' . $err->getMessage(), $err->getCode(), $err);
		}
	}

	/**
	 * Gets a customer by filter.
	 *
	 * @param array $filter
	 *
	 * @return array|null
	 * @throws PleskException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function getCustomer(array $filter): ?array
	{
		try
		{
			$customer = $this->client->request([
				'customer' => [
					'get' => [
						'filter' => $filter,
						'dataset' => [
							'gen_info' => '',
							'stat' => ''
						]
					]
				]
			]);

			$customer = PleskApiUtil::flatten($customer, 'customer', 'get', 'result');

			if (ArrayUtil::isAssociativeArray($customer))
				return PleskApiUtil::flatten($customer, 'data');

			throw new Exception('Multiple customers found, only one is allowed!');
		}
		catch (Exception $err)
		{
			throw new PleskException('Could not fetch customer: ' . $err->getMessage(), $err->getCode(), $err);
		}
	}

	/**
	 * Gets customers by filter.
	 *
	 * @param array $filter
	 *
	 * @return array|null
	 * @throws PleskException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function getCustomers(array $filter = []): ?array
	{
		try
		{
			$customer = $this->client->request([
				'customer' => [
					'get' => [
						'filter' => $filter,
						'dataset' => [
							'gen_info' => '',
							'stat' => ''
						]
					]
				]
			]);

			$customers = PleskApiUtil::flatten($customer, 'customer', 'get', 'result');
			$customers = array_map(fn(array $customer): array => $customer['data'], $customers);

			return $customers;
		}
		catch (Exception $err)
		{
			throw new PleskException('Could not fetch customers: ' . $err->getMessage(), $err->getCode(), $err);
		}
	}

}
