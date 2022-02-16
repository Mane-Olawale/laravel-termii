<?php

/*
 * This file is part of the Termii Client.
 *
 * (c) Ilesanmi Olawale Adedoun Twitter: @mane_olawale
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ManeOlawale\Laravel\Termii\Testing;

use Psr\Http\Message\ResponseInterface;
use ManeOlawale\Laravel\Termii\Testing\TermiiFake;
use ManeOlawale\Termii\HttpClient\HttpManagerInterface;

class FakeHttpManager implements HttpManagerInterface
{
    /**
     * Termii instance
     * @var \ManeOlawale\Laravel\Termii\Testing\TermiiFake
     */
    protected $fake;

    /**
     * Contructor
     *
     * @since 1.0
     *
     * @param \GuzzleHttp\Client $http
     */
    public function __construct(TermiiFake $fake)
    {
        $this->fake = $fake;
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
    public function request(string $method, string $url, array $data): ResponseInterface
    {
        $alias = $this->fake->getPathAlias(trim(parse_url($url, PHP_URL_PATH), '/'));

        return $this->fake->request($alias, $method, $url, $data);
    }
}
