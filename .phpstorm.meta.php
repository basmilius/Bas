<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace PHPSTORM_META;

expectedArguments(
	\Columba\Util\Stopwatch::stop(),
	2,
	\Columba\Util\Stopwatch::UNIT_MICROSECONDS,
	\Columba\Util\Stopwatch::UNIT_MILLISECONDS,
	\Columba\Util\Stopwatch::UNIT_NANOSECONDS,
	\Columba\Util\Stopwatch::UNIT_SECONDS
);

expectedArguments(
	\Columba\Router\Context::setResponseCode(),
	0,
	\Columba\Http\ResponseCode::CONTINUE,
	\Columba\Http\ResponseCode::SWITCHING_PROTOCOLS,
	\Columba\Http\ResponseCode::PROCESSING,
	\Columba\Http\ResponseCode::EARLY_HINTS,
	\Columba\Http\ResponseCode::OK,
	\Columba\Http\ResponseCode::CREATED,
	\Columba\Http\ResponseCode::ACCEPTED,
	\Columba\Http\ResponseCode::NON_AUTHORITATIVE_INFORMATION,
	\Columba\Http\ResponseCode::NO_CONTENT,
	\Columba\Http\ResponseCode::RESET_CONTENT,
	\Columba\Http\ResponseCode::PARTIAL_CONTENT,
	\Columba\Http\ResponseCode::MULTI_STATUS,
	\Columba\Http\ResponseCode::ALREADY_REPORTED,
	\Columba\Http\ResponseCode::IM_USED,
	\Columba\Http\ResponseCode::MULTIPLE_CHOICES,
	\Columba\Http\ResponseCode::MOVED_PERMANENTLY,
	\Columba\Http\ResponseCode::FOUND,
	\Columba\Http\ResponseCode::SEE_OTHER,
	\Columba\Http\ResponseCode::NOT_MODIFIED,
	\Columba\Http\ResponseCode::USE_PROXY,
	\Columba\Http\ResponseCode::SWITCH_PROXY,
	\Columba\Http\ResponseCode::TEMPORARY_REDIRECT,
	\Columba\Http\ResponseCode::PERMANENT_REDIRECT,
	\Columba\Http\ResponseCode::BAD_REQUEST,
	\Columba\Http\ResponseCode::UNAUTHORIZED,
	\Columba\Http\ResponseCode::PAYMENT_REQUIRED,
	\Columba\Http\ResponseCode::FORBIDDEN,
	\Columba\Http\ResponseCode::NOT_FOUND,
	\Columba\Http\ResponseCode::METHOD_NOT_ALLOWED,
	\Columba\Http\ResponseCode::NOT_ACCEPTABLE,
	\Columba\Http\ResponseCode::PROXY_AUTHENTICATION_REQUIRED,
	\Columba\Http\ResponseCode::REQUEST_TIMEOUT,
	\Columba\Http\ResponseCode::CONFLICT,
	\Columba\Http\ResponseCode::GONE,
	\Columba\Http\ResponseCode::LENGTH_REQUIRED,
	\Columba\Http\ResponseCode::PRECONDITION_FAILED,
	\Columba\Http\ResponseCode::PAYLOAD_TOO_LARGE,
	\Columba\Http\ResponseCode::URI_TOO_LONG,
	\Columba\Http\ResponseCode::UNSUPPORTED_MEDIA_TYPE,
	\Columba\Http\ResponseCode::RANGE_NOT_SATISFIABLE,
	\Columba\Http\ResponseCode::EXPECTATION_FAILED,
	\Columba\Http\ResponseCode::IM_A_TEAPOT,
	\Columba\Http\ResponseCode::MISDIRECTED_REQUEST,
	\Columba\Http\ResponseCode::UNPROCESSABLE_ENTITY,
	\Columba\Http\ResponseCode::LOCKED,
	\Columba\Http\ResponseCode::FAILED_DEPENDENCY,
	\Columba\Http\ResponseCode::UPGRADE_REQUIRED,
	\Columba\Http\ResponseCode::PRECONDITION_REQUIRED,
	\Columba\Http\ResponseCode::TOO_MANY_REQUESTS,
	\Columba\Http\ResponseCode::REQUEST_HEADER_FIELDS_TOO_LARGE,
	\Columba\Http\ResponseCode::UNAVAILABLE_FOR_LEGAL_REASONS,
	\Columba\Http\ResponseCode::INTERNAL_SERVER_ERROR,
	\Columba\Http\ResponseCode::NOT_IMPLEMENTED,
	\Columba\Http\ResponseCode::BAD_GATEWAY,
	\Columba\Http\ResponseCode::SERVICE_UNAVAILABLE,
	\Columba\Http\ResponseCode::GATEWAY_TIMEOUT,
	\Columba\Http\ResponseCode::HTTP_VERSION_NOT_SUPPORTED,
	\Columba\Http\ResponseCode::VARIANT_ALSO_NEGOTIATES,
	\Columba\Http\ResponseCode::INSUFFICIENT_STORAGE,
	\Columba\Http\ResponseCode::LOOP_DETECTED,
	\Columba\Http\ResponseCode::NOT_EXTENDED,
	\Columba\Http\ResponseCode::NETWORK_AUTHENTICATION_REQUIRED
);
