<?php

/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Drupal\civiremote_funding\Form\ResponseHandler;

use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

final class NewFundingCaseFormResponseHandler implements FormResponseHandlerInterface {

  public function __construct(
    private readonly FormResponseHandlerInterface $formResponseHandler,
  ) {}

  public function handleSubmitResponse(
    Request $request,
    FormSubmitResponse $submitResponse,
    FormStateInterface $formState
  ): void {
    $this->formResponseHandler->handleSubmitResponse($request, $submitResponse, $formState);

    /*
     * Redirect to the form to add a new application after the funding case has
     * been created, if the action to copy an application into a new funding
     * case had been chosen.
     */
    $copyDataFromId = $request->query->get('copyDataFromId');
    if (NULL !== $copyDataFromId && 'loadEntity' === $submitResponse->getAction()) {
      $formState->setRedirect(
        'civiremote_funding.case_application_add',
        ['fundingCaseId' => $submitResponse->getEntityId()],
        ['query' => ['copyDataFromId' => $copyDataFromId]]
      );
    }
  }

}
