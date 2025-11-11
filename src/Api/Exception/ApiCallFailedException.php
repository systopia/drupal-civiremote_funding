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

namespace Drupal\civiremote_funding\Api\Exception;

use CMRF\Core\Call;

/**
 * @phpstan-type api3ErrorReplyT array{
 *   error_message: string,
 *   is_error: 1,
 * }
 *
 * @phpstan-type api4ErrorReplyT array{
 *   error_message: string,
 *   error_code: int|string,
 *   status: int,
 * }
 */
class ApiCallFailedException extends \RuntimeException implements ExceptionInterface {

  private Call $call;

  public static function fromCall(Call $call): self {
    /** @phpstan-var api3ErrorReplyT|api4ErrorReplyT $reply */
    $reply = $call->getReply();

    if (403 === ($reply['status'] ?? NULL)) {
      return new ApiCallUnauthorizedException($call, $reply['error_message'], (int) $reply['error_code']);
    }

    return new self($call, $reply['error_message'], (int) ($reply['error_code'] ?? 0));
  }

  final public function __construct(Call $call, string $message = '', int $code = 0, ?\Throwable $previous = NULL) {
    parent::__construct($message, $code, $previous);
    $this->call = $call;
  }

  public function getCall(): Call {
    return $this->call;
  }

  /**
   * @return int|null
   *   The status code returned in the CiviCRM APIv4 reply. NULL for APIv3
   *   requests.
   */
  public function getStatusCode(): ?int {
    return $this->call->getReply()['status'] ?? NULL;
  }

}
