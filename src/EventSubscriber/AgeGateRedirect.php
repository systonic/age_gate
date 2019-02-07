<?php

namespace Drupal\age_gate\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Class AgeGateRedirect.
 */
class AgeGateRedirect implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['redirect'];
    return $events;
  }

  /**
   * Redirect to the form or not.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event.
   */
  public function redirect(FilterResponseEvent $event) {
    $redirect = TRUE;
    $redirect_url = 'age_gate.form';
    $user = \Drupal::currentUser();
    $config = \Drupal::config('age_gate.settings');
    $request = $event->getRequest();
    $session = $request->getSession();
    $current_path = \Drupal::service('path.current')->getPath();
    $alias_manager = \Drupal::service('path.alias_manager');
    $alias_path = $alias_manager->getAliasByPath($current_path);

    // Skip redirect when already on the form.
    if ($request->attributes->get('_route') === $redirect_url) {
      $redirect = FALSE;
    }

    // Skip redirect when session is set.
    if ($session->get('age_gate_verified')) {
      $redirect = FALSE;
    }

    // Explode the form field to get each line.
    $skip_urls = explode("\n", $config->get('age_gate_urls_to_skip'));
    $skip_urls[] = '/admin';

    // For each one of the lines we want to trim white space and empty lines.
    foreach ($skip_urls as $key => $url) {
      if (empty($url)) {
        continue;
      }
      // To be sure we match the proper string, we need to trim it.
      $url = trim($url);
      // Now because Drupal 8 works with paths that start from '/', we need to prepend it if needed.
      if (strpos($url, '/') !== 0) {
        $url = '/' . $url;
      }
      // Skip redirect if the URL is equal alias in the admin field.
      if ($alias_manager->getAliasByPath($url) == $alias_path) {
        $redirect = FALSE;
      }
    }

    // Now we need to explode the age_gate_user_agents field to separate lines.
    $user_agents = explode("\n", $config->get('age_gate_user_agents'));
    $http_user_agent = \Drupal::request()->server->get('HTTP_USER_AGENT');

    // For each one of the lines we want to trim white space and empty lines.
    foreach ($user_agents as $key => $user_agent) {
      // If a user has string from $user_agent.
      if (empty($user_agent)) {
        continue;
      }
      // To be sure we match proper string, we need to trim it.
      $user_agent = trim($user_agent);
      // Skip redirect for the user agent is in the admin field.
      if ($user_agent == $http_user_agent) {
        $redirect = FALSE;
      }
    }

    if ($user->id() == 0 && $redirect) {
      $session->set('age_gate_path', $current_path);
      $response = new RedirectResponse(Url::fromRoute($redirect_url)->toString(), 302);
      $event->setResponse($response);
    }
  }

}
