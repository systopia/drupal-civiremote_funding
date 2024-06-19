<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\civiremote_funding\Form\RequestHandler;

use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\civiremote_funding\Api\Form\FormValidationResponse;
use Drupal\civiremote_funding\Api\Form\FundingForm;
use Symfony\Component\HttpFoundation\Request;

interface FormRequestHandlerInterface {

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getForm(Request $request): FundingForm;

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @return \Drupal\civiremote_funding\Api\Form\FormValidationResponse
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function validateForm(Request $request, array $data): FormValidationResponse;

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @return \Drupal\civiremote_funding\Api\Form\FormSubmitResponse
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function submitForm(Request $request, array $data): FormSubmitResponse;

}
