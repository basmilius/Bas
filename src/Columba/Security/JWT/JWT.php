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

namespace Columba\Security\JWT;

/**
 * Class JWT
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Security\JWT
 * @since 1.5.0
 */
final class JWT
{

	/**
	 * @var int|null
	 */
	public static $currentTime = null;

	/**
	 * @var int
	 */
	public static $leeway = 0;

	/**
	 * @var array
	 */
	public static $supportedAlgorithms = [
		'HS256' => ['hash_hmac', 'SHA256'],
		'HS512' => ['hash_hmac', 'SHA512'],
		'HS384' => ['hash_hmac', 'SHA384'],
		'RS256' => ['openssl', 'SHA256'],
		'RS384' => ['openssl', 'SHA384'],
		'RS512' => ['openssl', 'SHA512'],
	];

	/**
	 * Decodes a JWT string into an array.
	 *
	 * @param string   $jwt
	 * @param string[] $keys
	 * @param array    $allowedAlgorithms
	 *
	 * @return array
	 * @throws JWTException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @uses JWT::jsonDecode()
	 * @uses JWT::urlsafeB64Decode()
	 */
	public static function decode(string $jwt, array $keys, array $allowedAlgorithms = []): array
	{
		$currentTime = static::$currentTime ?? time();

		if (empty($keys))
			throw new JWTException('At least one key is required.', JWTException::ERR_INVALID_ARGUMENT);

		$segments = explode('.', $jwt);

		if (count($segments) !== 3)
			throw new JWTException('Wrong number of segments.', JWTException::ERR_UNEXPECTED_ARGUMENT);

		[$header64, $payload64, $signature64] = $segments;

		$header = static::jsonDecode(static::urlsafeB64Decode($header64));
		$payload = static::jsonDecode(static::urlsafeB64Decode($payload64));
		$signature = static::urlsafeB64Decode($signature64);

		if ($header === null || $payload === null || $signature === null)
			throw new JWTException('Invalid encoding of segment.', JWTException::ERR_UNEXPECTED_ARGUMENT);

		if (!isset($header['alg']))
			throw new JWTException('Unknown algorithm.', JWTException::ERR_UNEXPECTED_ARGUMENT);

		if (!isset(static::$supportedAlgorithms[$header['alg']]))
			throw new JWTException('Algorithm not supported.', JWTException::ERR_UNSUPPORTED);

		if (count($allowedAlgorithms) > 0 && !in_array($header['alg'], $allowedAlgorithms))
			throw new JWTException('Algorithm not allowed.', JWTException::ERR_UNEXPECTED_ARGUMENT);

		if (count($keys) > 1)
		{
			if (isset($header['kid']))
				if (isset($keys[$header['kid']]))
					$key = $keys[$header['kid']];
				else
					throw new JWTException('kid is invalid, key does not exist.', JWTException::ERR_UNEXPECTED_ARGUMENT);
			else
				throw new JWTException('kid is missing in JWT payload.', JWTException::ERR_UNEXPECTED_ARGUMENT);
		}
		else
		{
			$key = array_shift($keys);
		}

		if (!static::verify(sprintf('%s.%s', $header64, $payload64), $signature, $key, $header['alg']))
			throw new JWTException('Invalid signature.', JWTException::ERR_INVALID_SIGNATURE);

		if (isset($payload['nbf']) && $payload['nbf'] > ($currentTime + static::$leeway))
			throw new JWTException(sprintf('Cannot handle token prior to %s', date('Y-m-d\TH:i:sO', $payload['nbf'])), JWTException::ERR_NOT_YET_VALID);

		if (isset($payload['iat']) && $payload['iat'] > ($currentTime + static::$leeway))
			throw new JWTException(sprintf('Cannot handle token prior to %s', date('Y-m-d\TH:i:sO', $payload['iat'])), JWTException::ERR_NOT_YET_VALID);

		if (isset($payload['exp']) && ($currentTime - static::$leeway) >= $payload['exp'])
			throw new JWTException('Expired token', JWTException::ERR_EXPIRED);

		return $payload;
	}

	/**
	 * Converts and signs an array into a JWT string.
	 *
	 * @param array  $payload
	 * @param string $key
	 * @param string $algorithmName
	 * @param null   $keyId
	 * @param array  $headers
	 *
	 * @return string
	 * @throws JWTException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @uses JWT::jsonEncode()
	 * @uses JWT::urlsafeB64Encode()
	 */
	public static function encode(array $payload, string $key, string $algorithmName = 'HS256', $keyId = null, array $headers = []): string
	{
		$headers['typ'] = 'JWT';
		$headers['alg'] = $algorithmName;

		if ($keyId !== null)
			$headers['kid'] = $keyId;

		$segments = [];
		$segments[] = static::urlsafeB64Encode(static::jsonEncode($headers));
		$segments[] = static::urlsafeB64Encode(static::jsonEncode($payload));

		$plainToken = implode('.', $segments);

		$signature = static::sign($plainToken, $key, $algorithmName);
		$segments[] = self::urlsafeB64Encode($signature);

		return implode('.', $segments);
	}

	/**
	 * Signs a string with the given key and algorithm.
	 *
	 * @param string $message
	 * @param string $key
	 * @param string $algorithmName
	 *
	 * @return string
	 * @throws JWTException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function sign(string $message, string $key, string $algorithmName): string
	{
		if (!isset(static::$supportedAlgorithms[$algorithmName]))
			throw new JWTException('Algorithm not supported.', JWTException::ERR_UNSUPPORTED);

		[$function, $algorithm] = static::$supportedAlgorithms[$algorithmName];

		switch ($function)
		{
			case 'hash_hmac':
				return hash_hmac($algorithm, $message, $key, true);

			case 'openssl':
				$signature = '';
				$success = openssl_sign($message, $signature, $key, $algorithm);

				if (!$success)
					throw new JWTException('Unable to sign data.', JWTException::ERR_OPENSSL);

				return $signature;

			default:
				throw new JWTException('Algorithm not supported.', JWTException::ERR_UNSUPPORTED);
		}
	}

	/**
	 * Verifies a signature with the message, key and algorithm. Not all methods
	 * are symmetric, so we must have a separate verify and sign method.
	 *
	 * @param string $message
	 * @param string $signature
	 * @param string $key
	 * @param string $algorithmName
	 *
	 * @return bool
	 * @throws JWTException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	private static function verify(string $message, string $signature, string $key, string $algorithmName): bool
	{
		if (!isset(static::$supportedAlgorithms[$algorithmName]))
			throw new JWTException('Algorithm not supported.', JWTException::ERR_UNSUPPORTED);

		[$function, $algorithm] = static::$supportedAlgorithms[$algorithmName];

		switch ($function)
		{
			case 'openssl':
				$result = openssl_verify($message, $signature, $key, $algorithm);

				if ($result === -1)
					throw new JWTException(openssl_error_string(), JWTException::ERR_OPENSSL);

				return $result === 1;

			case'hash_hmac':
			default:
				$hash = hash_hmac($algorithm, $message, $key, true);

				return hash_equals($signature, $hash);
		}
	}

	/**
	 * Decodes a JSON string into an array.
	 *
	 * @param string $input
	 *
	 * @return mixed
	 * @throws JWTException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function jsonDecode(string $input)
	{
		$data = json_decode($input, true, 512, JSON_BIGINT_AS_STRING);

		if (($errorCode = json_last_error()) !== JSON_ERROR_NONE)
			self::onJSONError($errorCode);

		if ($data === null && $input !== 'null')
			throw new JWTException('NULL result with non-NULL input.', JWTException::ERR_NULL_RESULT);

		return $data;
	}

	/**
	 * Encodes an array into a JSON string.
	 *
	 * @param mixed $data
	 *
	 * @return string
	 * @throws JWTException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function jsonEncode($data): string
	{
		$json = json_encode($data);

		if (($errorCode = json_last_error()) !== JSON_ERROR_NONE)
			static::onJSONError($errorCode);

		if ($json === 'null' && $data !== null)
			throw new JWTException('NULL result with non-NULL data.', JWTException::ERR_NULL_RESULT);

		return $json;
	}

	/**
	 * Decodes a string with url-safe base64.
	 *
	 * @param string $input
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function urlsafeB64Decode(string $input): string
	{
		$remainder = strlen($input) % 4;

		if ($remainder)
		{
			$padlen = 4 - $remainder;
			$input .= str_repeat('=', $padlen);
		}

		return base64_decode(strtr($input, '-_', '+/'));
	}

	/**
	 * Encodes a string with url-safe base64.
	 *
	 * @param string $input
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function urlsafeB64Encode(string $input): string
	{
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}

	/**
	 * Invoked when a JSON error occurs.
	 *
	 * @param int $errorCode
	 *
	 * @throws JWTException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	private static function onJSONError(int $errorCode): void
	{
		$messages = [
			JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
			JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
			JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
			JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
			JSON_ERROR_UTF8 => 'Malformed UTF-8 characters'
		];

		throw new JWTException($messages[$errorCode] ?? sprintf('Unknown JSON error: %d', $errorCode), JWTException::ERR_JSON_ERROR);
	}

}
