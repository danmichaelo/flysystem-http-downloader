<?php

namespace spec\Indigo\Flysystem;

use League\Flysystem\Filesystem;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamInterface as Stream;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DownloaderSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem, HttpClient $httpClient)
    {
        $this->beConstructedWith($filesystem, $httpClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Indigo\Flysystem\Downloader');
    }

    function it_downloads_a_request(Filesystem $filesystem, HttpClient $httpClient, Request $request, Response $response, Stream $stream)
    {
        $httpClient->sendRequest($request)->willReturn($response);
        $response->getBody()->willReturn($stream);
        $stream->detach()->willReturn(tmpfile());

        $filesystem->putStream('path/to/file', Argument::type('resource'))->willReturn(true);

        $this->download($request, 'path/to/file')->shouldReturn(true);
    }

    function it_returns_false_when_body_is_empty(Filesystem $filesystem, HttpClient $httpClient, Request $request, Response $response)
    {
        $httpClient->sendRequest($request)->willReturn($response);
        $response->getBody()->willReturn(null);

        $filesystem->putStream('path/to/file', Argument::type('resource'))->shouldNotBeCalled();

        $this->download($request, 'path/to/file')->shouldReturn(false);
    }

    function it_handles_ivory_interface_incompatibility(Filesystem $filesystem, HttpClient $httpClient, Request $request, Response $response, Stream $stream)
    {
        $httpClient->sendRequest($request)->willReturn($response);
        $response->getBody()->willReturn($stream);
        $stream->detach()->willReturn('text');

        $filesystem->putStream('path/to/file', Argument::type('resource'))->shouldNotBeCalled();
        $filesystem->put('path/to/file', 'text')->willReturn(true);

        $this->download($request, 'path/to/file')->shouldReturn(true);
    }
}
