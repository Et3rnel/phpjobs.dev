<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractHttp
{
    /** @var Client */
    protected $client;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function createClient(array $config = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(array_keys($config));

        $this->configureOptions($resolver);

        if (array_key_exists('handler', $config)) {
            $handlerStack = $config['handler'];
        } else {
            $handlerStack = HandlerStack::create();
        }

        // We add the Guzzle http queries logging
        $handlerStack->push(
            Middleware::log(
                $this->logger,
                new MessageFormatter('{req_body} - {res_body}')
            )
        );

        $this->client = new Client($resolver->resolve($config));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'verify' => 'C:\Projects\udemy\private\cacert.pem', // TODO : check how to make this way better
            'http_errors' => false, // TODO : check if it's really necessary to set it false or if we can handle errors
        ]);
    }
}