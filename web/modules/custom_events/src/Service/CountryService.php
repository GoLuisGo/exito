<?php

declare(strict_types=1);

namespace Drupal\custom_events\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

final class CountryService {

  public function __construct(
    protected ClientInterface $httpClient,
    protected CacheBackendInterface $cache,
    protected LoggerInterface $logger,
  ) {}

  public function getCountries(): array {
    $cid = 'custom_events:countries';
    $cached = $this->cache->get($cid);

    // aqui primero reviso cache para no pegarle a la api en cada request.
    if ($cached) {
      return $cached->data;
    }

    try {
      $response = $this->httpClient->request('GET', 'https://restcountries.com/v3.1/all?fields=name,cca2', [
        'timeout' => 10,
      ]);

      $data = json_decode((string) $response->getBody(), TRUE);
      $countries = [];

      if (is_array($data)) {
        foreach ($data as $item) {
          $code = $item['cca2'] ?? '';
          $name = $item['name']['common'] ?? '';

          if ($code && $name) {
            $countries[$code] = $name;
          }
        }
      }

      asort($countries);

      // aqui guardo 24 horas de cache para no consultar siempre.
      $this->cache->set($cid, $countries, time() + 86400);

      return $countries;
    }
    catch (\Throwable $e) {
      // aqui registro el error y devuelvo arreglo vacio para no romper el flujo.
      $this->logger->error('Error consultando pa\u00edses: @message', [
        '@message' => $e->getMessage(),
      ]);
      return [];
    }
  }

}