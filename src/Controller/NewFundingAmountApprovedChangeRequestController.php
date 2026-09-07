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

declare(strict_types = 1);

namespace Drupal\civiremote_funding\Controller;

use Drupal\civiremote_funding\Form\NewFundingAmountApprovedChangeRequestForm;
use Drupal\Core\Controller\ControllerBase;

final class NewFundingAmountApprovedChangeRequestController extends ControllerBase {

  /**
   * @return array<int|string, mixed>
   */
  public function form(int $fundingCaseId): array {
    return $this->formBuilder()
      ->getForm(NewFundingAmountApprovedChangeRequestForm::class, $fundingCaseId);
  }

}
