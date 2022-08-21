<?php

namespace ManeOlawale\Laravel\Termii\Testing;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Collection;
use ManeOlawale\Laravel\Termii\Termii;
use Psr\Http\Message\ResponseInterface;

class TermiiFake
{
    /**
     * Tokens sent durring runtime
     * @var \ManeOlawale\Laravel\Termii\Termii
     */
    protected $termii;

    /**
     * Tokens sent durring runtime
     * @var array
     */
    protected $record;

    /**
     * Response stacks
     * @var array
     */
    protected $responses;

    /**
     * Fallback Response
     * @var \GuzzleHttp\Psr7\Response
     */
    protected $fallbackResponse;

    public function __construct(Termii $termii)
    {
        $this->record = [];
        $this->termii = $termii;
        $this->fallbackResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{}'
        );
    }

    /**
     * Set Up termii object for testing mode
     * @since 0.0.2
     *
     * @return void
     */
    public function setUpTestMode()
    {
        $this->termii->client()->setHttpManager(new FakeHttpManager($this));
        foreach (array_keys($this->aliasMap()) as $value) {
            $this->record[$value] = new Collection([]);
        }
    }

    /**
     * return a mocked responses
     *
     * @since 1.0
     *
     * @param string $alias
     * @param string $method
     * @param string $route
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(string $alias, string $method, string $url, array $data): ResponseInterface
    {
        $url = new Uri($url);
        if ($method === 'GET') {
            $url = $url->withQuery(http_build_query($data['query']));
            $body = null;
        } else {
            $body = json_encode($data['body']);
        }

        $request = new Request(
            $method,
            $url,
            $data['headers'],
            $body
        );

        if ($sequence = $this->responseSequence($alias)) {
            $response = $sequence->next() ?? $this->fallbackResponse();
        } else {
            $response = $this->fallbackResponse();
        }

        $this->recordResponse($alias, $request, $response);

        return $response;
    }

    /**
     * Get response sequence
     * @since 0.0.2
     *
     * @param string $alias
     * @param \GuzzleHttp\Psr7\Request $request
     * @param \GuzzleHttp\Psr7\Response $response
     * @return void
     */
    public function recordResponse(string $alias, Request $request, Response $response)
    {
        if (!isset($this->record[$alias])) {
            $this->record[$alias] = new Collection([]);
        }

        $this->record[$alias]->add([
            'alias' => $alias,
            'request' => $request,
            'response' => $response,
            'successful' => $response->getStatusCode() >= 100 && $response->getStatusCode() <= 299
        ]);
    }

    /**
     * Get response sequence
     * @since 0.0.2
     *
     * @param string $alias
     * @return Sequence
     */
    public function responseSequence(string $alias)
    {
        return $this->responses[$alias] ?? null;
    }

    /**
     * Get or Set fallback response
     * @since 0.0.2
     *
     * @param \GuzzleHttp\Psr7\Response $response
     * @return \GuzzleHttp\Psr7\Response
     */
    public function fallbackResponse(Response $response = null)
    {
        if ($response) {
            $this->fallbackResponse = $response;
        }

        return $this->fallbackResponse;
    }

    /**
     * Mock a response with sequence
     * @since 0.0.2
     *
     * @param string $alias
     * @param \ManeOlawale\Laravel\Termii\Testing\Sequence $sequence
     * @return void
     */
    public function mock(string $alias, Sequence $sequence = null)
    {
        $this->responses[$alias] = $sequence;
        return $this;
    }

    /**
     * Get the alias of a path
     * @since 0.0.2
     *
     * @param string $alias
     *
     * @return \Illuminate\Support\Collection
     */
    public function responses(string $alias): Collection
    {
        return $this->record[$alias];
    }

    /**
     * Get the alias of a path
     * @since 0.0.2
     *
     * @return string
     */
    public function getPath(string $alias = null)
    {
        return $this->aliasMap()[$alias];
    }

    /**
     * Get the alias of a path
     * @since 0.0.2
     *
     * @return string
     */
    public function getPathAlias(string $path = null)
    {
        return array_flip($this->aliasMap())[$path];
    }

    /**
     * Alias map
     * @since 0.0.2
     *
     * @return array
     */
    protected function aliasMap()
    {
        return [
            'sender' => 'api/sender-id',
            'request' => 'api/sender-id/request',
            'send' => 'api/sms/send',
            'number' => 'api/sms/number/send',
            'template' => 'api/send/template',
            'otp' => 'api/sms/otp/send',
            'verify' => 'api/sms/otp/verify',
            'inapp' => 'api/sms/otp/generate',
            'balance' => 'api/get-balance',
            'search' => 'api/insight/number/query',
            'inbox' => 'api/sms/inbox',
            'dnd' => 'api/check/dnd'
        ];
    }
}
