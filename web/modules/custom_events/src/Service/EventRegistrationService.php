<?php

declare(strict_types=1);

namespace Drupal\custom_events\Service;

use Drupal\Core\Database\Connection;
use Drupal\Component\Datetime\TimeInterface;

final class EventRegistrationService {

  public function __construct(
    protected Connection $database,
    protected TimeInterface $time,
  ) {}

  public function isRegistered(int $eventNid, int $uid): bool {
    // aqui busco si ya existe una fila para este evento y este usuario.
    $result = $this->database->select('custom_events_registrations', 'r')
      ->fields('r', ['id'])
      ->condition('event_nid', $eventNid)
      ->condition('uid', $uid)
      ->range(0, 1)
      ->execute()
      ->fetchField();

    return !empty($result);
  }

  public function register(int $eventNid, int $uid): bool {
    // valido que el usuario no se haya registrado antes para evitar duplicados.
    if ($this->isRegistered($eventNid, $uid)) {
      return FALSE;
    }

    // aqui inserto el registro con timestamp actual del sistema.
    $this->database->insert('custom_events_registrations')
      ->fields([
        'event_nid' => $eventNid,
        'uid' => $uid,
        'created' => $this->time->getCurrentTime(),
      ])
      ->execute();

    return TRUE;
  }

  public function countByEvent(int $eventNid): int {
    // aqui saco el total de inscritos para mostrarlo en el listado.
    return (int) $this->database->select('custom_events_registrations', 'r')
      ->condition('event_nid', $eventNid)
      ->countQuery()
      ->execute()
      ->fetchField();
  }
}
