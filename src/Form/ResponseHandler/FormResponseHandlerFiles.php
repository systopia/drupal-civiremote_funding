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
use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\civiremote_funding\JsonForms\Callbacks\FileUploadCallback;
use Drupal\Core\Form\FormStateInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class FormResponseHandlerFiles implements FormResponseHandlerInterface {

  private FundingFileManager $fundingFileManager;

  private LoggerInterface $logger;

  public function __construct(
    FundingFileManager $fundingFileManager,
    LoggerInterface $logger
  ) {
    $this->fundingFileManager = $fundingFileManager;
    $this->logger = $logger;
  }

  public function handleSubmitResponse(
    Request $request,
    FormSubmitResponse $submitResponse,
    FormStateInterface $formState
  ): void {
    $fundingFilesByUri = $this->getFundingFilesByUri($formState);

    foreach ($submitResponse->getFiles() as $submittedUri => $civiUri) {
      if ($submittedUri === $civiUri) {
        // File was not changed.
        continue;
      }

      $fundingFile = $fundingFilesByUri[$submittedUri] ?? NULL;
      if (NULL === $fundingFile) {
        $this->logger->error(sprintf('No funding file found for "%s"', $submittedUri));
      }
      else {
        $fundingFile->setCiviUri($civiUri);
        $this->fundingFileManager->save($fundingFile);
      }
    }
  }

  /**
   * @return array<string, \Drupal\civiremote_funding\Entity\FundingFileInterface>
   */
  private function getFundingFilesByUri(FormStateInterface $formState): array {
    /** @phpstan-var array<string, array{uri: string, fundingFile: \Drupal\civiremote_funding\Entity\FundingFile}> $filesProperty */
    $filesProperty = $formState->get(FileUploadCallback::FILES_PROPERTY_KEY) ?? [];
    $fundingFilesByUri = [];
    foreach ($filesProperty as $fileProps) {
      $fundingFilesByUri[$fileProps['uri']] = $fileProps['fundingFile'];
    }

    return $fundingFilesByUri;
  }

}
