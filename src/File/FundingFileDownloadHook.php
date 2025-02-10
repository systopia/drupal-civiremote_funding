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

namespace Drupal\civiremote_funding\File;

use Symfony\Component\HttpFoundation\Request;

final class FundingFileDownloadHook {

  private FundingFileDownloader $fundingFileDownloader;

  private FundingFileManager $fundingFileManager;

  public function __construct(FundingFileDownloader $fundingFileDownloader, FundingFileManager $fundingFileManager) {
    $this->fundingFileDownloader = $fundingFileDownloader;
    $this->fundingFileManager = $fundingFileManager;
  }

  /**
   * @return array<string, mixed>
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
   */
  public function __invoke(string $uri, Request $request): array {
    $fundingFile = $this->fundingFileManager->loadByFileUri($uri);
    if (NULL === $fundingFile) {
      return [];
    }

    if (TRUE !== $request->getSession()->get('civiremote_funding.accessFile:' . $fundingFile->getFileId())) {
      return [];
    }

    $this->fundingFileDownloader->download($fundingFile);
    $fundingFile->setLastAccess(time());
    $this->fundingFileManager->save($fundingFile);
    /** @var \Drupal\file\FileInterface $file */
    $file = $fundingFile->getFile();

    return [
      'Content-Type' => $file->getMimeType(),
      'Cache-Control' => 'private',
    ];
  }

}
