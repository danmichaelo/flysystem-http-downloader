<?php

/*
 * This file is part of the Flysystem HTTP Downloader package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Flysystem;

use League\Flysystem\Filesystem;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

/**
 * HTTP Downloader
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Downloader
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @param Filesystem $filesystem
     * @param HttpClient $httpClient
     */
    public function __construct(Filesystem $filesystem, HttpClient $httpClient)
    {
        $this->filesystem = $filesystem;
        $this->httpClient = $httpClient;
    }

    /**
     * Downloads a request
     *
     * @param RequestInterface $request
     *
     * @return boolean
     */
    public function download(RequestInterface $request, $path)
    {
        $response = $this->httpClient->sendRequest($request);

        if (!$body = $response->getBody()) {
            return false;
        }

        $stream = $body->detach();

        if (is_resource($stream)) {
            return $this->filesystem->putStream($path, $stream);
        }

        return $this->filesystem->put($path, $stream);
    }
}
