<?php

declare(strict_types=1);

namespace Drupal\custom_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\custom_events\Form\EventRegistrationForm;
use Drupal\custom_events\Service\EventRegistrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventListController extends ControllerBase {

  public function __construct(
    protected EventRegistrationService $registrationService,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('custom_events.registration_service')
    );
  }

  public function build(): array {
    $storage = $this->entityTypeManager()->getStorage('node');

    // aqui busco solo los eventos publicados y los ordeno por fecha
    $nids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'eventos')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->execute();

    $nodes = $storage->loadMultiple($nids);

    $events = [];
    // aqui cargo el mapa de paises para mostrar nombres.
    $countries = \Drupal::service('custom_events.country_service')->getCountries();
    $current_user = $this->currentUser();
    $form_builder = \Drupal::formBuilder();

    foreach ($nodes as $node) {
      $description = '';
      $country = '';
      $date = '';
      $event_id = (int) $node->id();
      $registered = FALSE;
      $registration_form = NULL;

      if ($node->hasField('field_descripcion') && !$node->get('field_descripcion')->isEmpty()) {
        $description = $node->get('field_descripcion')->value;
      }

      if ($node->hasField('field_pais') && !$node->get('field_pais')->isEmpty()) {
        $country_code = $node->get('field_pais')->value;
        $country = $countries[$country_code] ?? $country_code;
      }

      if ($node->hasField('field_fecha') && !$node->get('field_fecha')->isEmpty()) {
        // aqui trato el valor como fecha real de drupal
        $raw_date = (string) $node->get('field_fecha')->value;
        $timestamp = strtotime($raw_date);
        $date = $timestamp ? date('d/m/Y', $timestamp) : '';
      }

      if ($current_user->isAuthenticated()) {
        // aqui verifico si el usuario ya se registro para decidir que accion mostrar.
        $registered = $this->registrationService->isRegistered($event_id, (int) $current_user->id());

        if (!$registered) {
          // aqui renderizo el formulario de registro solo si todavia no esta inscrito.
          $registration_form = $form_builder->getForm(
            new EventRegistrationForm($this->registrationService, $event_id)
          );
        }
      }

      // aqui construyo el arreglo que le pasare al template twig.
      $events[] = [
        'id' => $event_id,
        'title' => $node->label(),
        'description' => $description,
        'country' => $country,
        'date' => $date,
        'registrations_count' => $this->registrationService->countByEvent($event_id),
        'registered' => $registered,
        'registration_form' => $registration_form,
      ];
    }

    return [
      '#theme' => 'custom_events_list',
      '#events' => $events,
      '#logged_in' => $current_user->isAuthenticated(),
      '#cache' => [
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => [
          'custom_events/custom_events.styles',
        ],
      ],
    ];
  }

}