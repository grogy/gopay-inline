<?php

namespace Contributte\GopayInline\Auth;

use Contributte\GopayInline\Api\Gateway;
use Contributte\GopayInline\Client;
use Contributte\GopayInline\Exception\AuthorizationException;
use Contributte\GopayInline\Http\Http;
use Contributte\GopayInline\Http\HttpClient;
use Contributte\GopayInline\Http\Request;
use Contributte\GopayInline\Http\Response;

class Oauth2Client implements Auth
{

	/** @var Client */
	private $client;

	/** @var HttpClient */
	private $http;

	/**
	 * @param Client $client
	 * @param Http $http
	 */
	public function __construct(Client $client, Http $http)
	{
		$this->client = $client;
		$this->http = $http;
	}

	/**
	 * @param array $credentials
	 * @return Response
	 */
	public function authenticate(array $credentials)
	{
		$request = new Request();

		// Set URL
		$request->setUrl(Gateway::getOauth2TokenUrl());

		// Prepare data
		$args = [
			'grant_type' => 'client_credentials',
			'scope' => $credentials['scope'],
		];
		$data = http_build_query($args);

		// Set-up headers
		$headers = [
			'Accept' => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded',
		];
		$request->setHeaders($headers);

		// Set-up opts
		$opts = [
			CURLOPT_SSL_VERIFYPEER => FALSE,
			CURLOPT_POST => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_USERPWD => $this->client->getClientId() . ':' . $this->client->getClientSecret(),
			CURLOPT_POSTFIELDS => $data,
		];
		$request->setOpts($opts);

		// Make request
		$response = $this->http->doRequest($request);

		if (!$response || !$response->getData()) {
			// cURL errors
			throw new AuthorizationException('Authorization failed', $response->getCode());
		} else if (isset($response->getData()->errors)) {
			// GoPay errors
			$error = $response->getData()->errors[0];
			throw new AuthorizationException(AuthorizationException::format($error), $error->error_code);
		}

		return $response;
	}

}
