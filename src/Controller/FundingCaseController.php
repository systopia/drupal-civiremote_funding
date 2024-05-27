<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Drupal\civiremote_funding\Controller;

use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\civiremote_funding\Form\FundingCaseForm;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This class provides the page content and the title used in breadcrumbs.
 *
 * The title shown on the page is prefixed by an additional string. For that
 * reason it's not possible to use a page in the corresponding View.
 */
final class FundingCaseController extends ControllerBase {

  private FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  /**
   * @return array<int|string, mixed>
   */
  public function content(Request $request, int $fundingCaseId): array {
    $fundingCase = $this->fundingApi->getFundingCase($fundingCaseId);
    if (NULL === $fundingCase) {
      throw new NotFoundHttpException();
    }

    $fundingCaseType = $this->fundingApi->getFundingCaseType($fundingCase->getFundingCaseTypeId());
    if (NULL === $fundingCaseType) {
      throw new NotFoundHttpException();
    }

    if (!$fundingCaseType->getIsCombinedApplication()) {
      // This controller is only for combined applications.
      throw new NotFoundHttpException();
    }

    $content = [
      '#title' => $fundingCase->getIdentifier(),
      '#type' => 'container',
    ];

    $content['apply_note'] = [
      '#markup' => '<p>' . $this->t(
        'Please do not forget to apply the combined application if everything is recorded or after additions.'
      ) . '</p>',
    ];

    $content['form'] = $this->formBuilder()->getForm(FundingCaseForm::class);

    if (in_array('application_create', $fundingCase->getPermissions(), TRUE)) {
      $content['add_link'] = [
        '#type' => 'link',
        '#title' => [
          '#type' => 'button',
          '#value' => $this->t(
            'Add @type',
            ['@type' => $fundingCaseType->getApplicationProcessLabel()]
          ),
        ],
        '#url' => Url::fromRoute(
          'civiremote_funding.case_application_add',
          ['fundingCaseId' => $fundingCaseId],
          ['query' => ['destination' => $request->getRequestUri()]]
        ),
      ];
    }

    $content['table'] = [
      '#type' => 'view',
      '#name' => 'civiremote_funding_combined_application_process_list',
      '#display_id' => 'embed_1',
      '#arguments' => [
        $fundingCaseId,
        $fundingCaseType->getApplicationProcessLabel(),
      ],
      '#embed' => TRUE,
    ];

    return $content;
  }

  public function title(int $fundingCaseId): ?string {
    $fundingCase = $this->fundingApi->getFundingCase($fundingCaseId,);

    return NULL === $fundingCase ? NULL : $fundingCase->getIdentifier();
  }

}
