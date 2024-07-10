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

namespace Drupal\civiremote_funding\Controller;

use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TokenFileDownloadController extends ControllerBase {

  private FundingFileManager $fundingFileManager;

  public function __construct(FundingFileManager $fundingFileManager) {
    $this->fundingFileManager = $fundingFileManager;
  }

  public function download(string $token, string $filename): Response {
    $fundingFile = $this->fundingFileManager->loadByTokenAndFilename($token, $filename);
    if (NULL === $fundingFile) {
      throw new NotFoundHttpException();
    }

    /** @var \Drupal\file\FileInterface $file */
    $file = $fundingFile->getFile();
    /** @var string $fileUri */
    $fileUri = $file->getFileUri();
    if (!is_file($fileUri)) {
      throw new NotFoundHttpException();
    }

    return new BinaryFileResponse(
      $fileUri,
      Response::HTTP_OK,
      ['Content-Type' => $file->getMimeType()],
      FALSE,
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    );
  }

}
