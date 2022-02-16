<?php

/*
 * This file is part of the Termii Client.
 *
 * (c) Ilesanmi Olawale Adedoun Twitter: @mane_olawale
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ManeOlawale\Laravel\Termii;

use Illuminate\Http\Client\PendingRequest;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Http;
use ManeOlawale\Laravel\Termii\Termii;
use ManeOlawale\Termii\HttpClient\HttpManagerInterface;

class HttpManager implements HttpManagerInterface
{
    /**
     * Termii instance
     * @var \ManeOlawale\Laravel\Termii\Termii
     */
    protected $termii;

    /**
     * Contructor
     *
     * @since 1.0
     *
     * @param \GuzzleHttp\Client $http
     */
    public function __construct(Termii $termii = null)
    {
        $this->termii = $termii;
    }

    /**
     * Handle requests
     *
     * @since 1.0
     *
     * @param string $method
     * @param string $route
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(string $method, string $route, array $data): ResponseInterface
    {
        if ($method === 'GET') {
            $body = [
                'query' => $data['query']
            ];
        } else {
            $body = [
                'body' => $data['body']
            ];
        }

        $headers = $data['headers'];

        return $this->http()->withHeaders($headers)->send($method, $route, $body)->toPsrResponse();
    }

    /**
     * Handle requests
     *
     * @since 1.0
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    public function http(): PendingRequest
    {
        return Http::asJson();
    }
}
