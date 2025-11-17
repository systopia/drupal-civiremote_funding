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

namespace Drupal\civiremote_funding\Form\ResponseHandler;

use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\civiremote_funding\FundingRedirectResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;

class FormResponseHandlerAction implements FormResponseHandlerInterface {

  use StringTranslationTrait;

  private MessengerInterface $messenger;

  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  public function handleSubmitResponse(
    Request $request,
    FormSubmitResponse $submitResponse,
    FormStateInterface $formState
  ): void {
    if ('showValidation' === $submitResponse->getAction()) {
      // We cannot add errors at this stage, though this actually cannot happen
      // because we have called the remote validation in the validation step.
      $this->messenger->addWarning($submitResponse->getMessage() ?? $this->t('Validation failed.'));
      $formState->disableRedirect();
    }
    else {
      if (NULL !== $submitResponse->getMessage()) {
        $this->messenger->addMessage($submitResponse->getMessage());
      }

      if ('closeForm' === $submitResponse->getAction()) {
        $formState->setRedirect('<front>');
      }
      elseif ('loadEntity' === $submitResponse->getAction()) {
        if ('FundingCase' === $submitResponse->getEntityType()) {
          $formState->setRedirect(
            'civiremote_funding.case',
            ['fundingCaseId' => $submitResponse->getEntityId()],
          );
        }
        else {
          // Unknown entity type.
          $formState->setRedirect('<front>');
        }
      }
      elseif ('reloadForm' === $submitResponse->getAction()) {
        $redirectUrl = $request->getUri();
        // @phpstan-ignore-next-line
        $redirectUrl = rtrim(preg_replace('/copyDataFromId=[0-9]+&?/', '', $redirectUrl), '&?');
        if (NULL !== $submitResponse->getCopyDataFromId()) {
          $redirectUrl .= str_contains($redirectUrl, '?') ? '&' : '?';
          $redirectUrl .= 'copyDataFromId=' . $submitResponse->getCopyDataFromId();
        }
        // The standard redirect cannot be used, if "destination" query
        // parameter is set. Drupal would then replace the redirect URL.
        $formState->setResponse(
          new FundingRedirectResponse($redirectUrl, FundingRedirectResponse::HTTP_SEE_OTHER)
        );
      }
      else {
        throw new \RuntimeException(sprintf('Unknown response action "%s"', $submitResponse->getAction()));
      }
    }
  }

}
