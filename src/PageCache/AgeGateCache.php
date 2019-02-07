<?php

namespace Drupal\age_gate\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\Session\SessionConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AgeGateCache.
 */
class AgeGateCache implements RequestPolicyInterface {

  /**
   * The session configuration.
   *
   * @var \Drupal\Core\Session\SessionConfigurationInterface
   */
  protected $sessionConfiguration;

  /**
   * Instantiates a new AgeGateCache object.
   *
   * @param \Drupal\Core\Session\SessionConfigurationInterface $session_configuration
   *   The session configuration.
   */
  public function __construct(SessionConfigurationInterface $session_configuration) {
    $this->sessionConfiguration = $session_configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if (!$this->sessionConfiguration->hasSession($request)) {
      return static::DENY;
    }

    return NULL;
  }

}
