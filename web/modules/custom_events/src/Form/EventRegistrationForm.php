<?php

declare(strict_types=1);

namespace Drupal\custom_events\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_events\Service\EventRegistrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EventRegistrationForm extends FormBase {

  public function __construct(
    protected EventRegistrationService $registrationService,
    protected int $eventId = 0,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('custom_events.registration_service'),
    );
  }

  public function getFormId(): string {
    return 'custom_events_registration_form_' . $this->eventId;
  }

  public function buildForm(array $form, FormStateInterface $form_state, int $event_id = 0): array {
    if ($event_id > 0) {
      $this->eventId = $event_id;
    }

    $form['#attributes']['id'] = 'custom-events-registration-form-' . $this->eventId;

    $form['event_id'] = [
      '#type' => 'hidden',
      '#value' => $this->eventId,
      '#attributes' => [
        'id' => 'edit-event-id-' . $this->eventId,
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Registrarse'),
      '#name' => 'submit_event_' . $this->eventId,
      '#attributes' => [
        'id' => 'edit-submit-' . $this->eventId,
      ],
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'wrapper' => 'event-actions-' . $this->eventId,
      ],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (!$this->currentUser()->isAuthenticated()) {
      $form_state->setErrorByName('submit', $this->t('Debes iniciar sesión.'));
      return;
    }

    $event_id = $this->getEventId($form_state);
    $user_id = (int) $this->currentUser()->id();

    if ($this->registrationService->isRegistered($event_id, $user_id)) {
      $form_state->setErrorByName('submit', $this->t('Ya estás inscrito en este evento.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $event_id = $this->getEventId($form_state);
    $user_id = (int) $this->currentUser()->id();

    $this->registrationService->register($event_id, $user_id);
  }

  public function ajaxSubmit(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    $event_id = $this->getEventId($form_state);
    $count = $this->registrationService->countByEvent($event_id);

    if ($form_state->hasAnyErrors()) {
      $messages = [
        '#type' => 'status_messages',
      ];

      $response->addCommand(
        new HtmlCommand(
          '#event-actions-' . $event_id,
          \Drupal::service('renderer')->renderRoot($messages)
        )
      );

      return $response;
    }

    $response->addCommand(new HtmlCommand(
      '#event-actions-' . $event_id,
      '<span class="btn-registered">Ya estás inscrito</span>'
    ));

    $response->addCommand(new HtmlCommand(
      '#event-count-' . $event_id,
      (string) $count
    ));

    return $response;
  }

  private function getEventId(FormStateInterface $form_state): int {
    $trigger = $form_state->getTriggeringElement();

    if (!empty($trigger['#name']) && preg_match('/^submit_event_(\d+)$/', (string) $trigger['#name'], $matches)) {
      return (int) $matches[1];
    }

    $build_info = $form_state->getBuildInfo();

    if (!empty($build_info['args'][0])) {
      return (int) $build_info['args'][0];
    }

    return (int) $form_state->getValue('event_id');
  }

}
