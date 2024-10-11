<?php

namespace Drupal\millboard_georedirect;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manages cookies for the GeoRedirect module.
 */
class CookieManager {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a CookieManager object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The Symfony RequestStack service.
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * Sets a cookie with a specified name and value.
   *
   * @param string $name
   *   The name of the cookie.
   * @param mixed $value
   *   The value to store in the cookie.
   */
  public function setCookie($name, $value) {
    // Set a cookie with an expiration time of one year (365 days).
    setcookie($name, $value, time() + 365 * 24 * 60 * 60, '/');
  }

  /**
   * Retrieves the value of a cookie by its name.
   *
   * @param string $name
   *   The name of the cookie to retrieve.
   *
   * @return mixed
   *   The value of the cookie if found, or NULL if not found.
   */
  public function getCookie($name) {
    $request = $this->requestStack->getCurrentRequest();
    // Use Symfony's request object to retrieve the cookie value.
    return $request->cookies->get($name);
  }

  /**
   * Deletes a cookie by name.
   *
   * @param string $name
   *   The name of the cookie to delete.
   */
  public function deleteCookie($name) {
    // Create a new response and clear the specified cookie.
    $response = new Response();
    $response->headers->clearCookie($name);
  }

}
