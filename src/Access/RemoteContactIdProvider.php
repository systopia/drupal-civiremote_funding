<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\civiremote_funding\Access;

use Assert\Assertion;
use Drupal\Core\Session\AccountProxyInterface;

final class RemoteContactIdProvider implements RemoteContactIdProviderInterface {

  private AccountProxyInterface $currentUser;

  public function __construct(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  public function getRemoteContactId(): string {
    $account = $this->currentUser->getAccount();
    $remoteContactId = $account->get('civiremote_id')->value;

    Assertion::string($remoteContactId);
    Assertion::notEmpty($remoteContactId);

    return $remoteContactId;
  }

}
