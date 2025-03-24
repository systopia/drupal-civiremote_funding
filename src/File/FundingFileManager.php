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

use Assert\Assertion;
use Drupal\civiremote_funding\Entity\FundingFileInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\file\FileStorageInterface;
use Drupal\file\FileUsage\FileUsageInterface;

class FundingFileManager {

  private FileRepositoryInterface $fileRepository;

  private FileStorageInterface $fileStorage;

  private FileSystemInterface $fileSystem;

  private FileUsageInterface $fileUsage;

  private FundingFileStorage $storage;

  private TokenGenerator $tokenGenerator;

  public function __construct(
    FileRepositoryInterface $fileRepository,
    FileStorageInterface $fileStorage,
    FileSystemInterface $fileSystem,
    FileUsageInterface $fileUsage,
    FundingFileStorage $storage,
    TokenGenerator $tokenGenerator
  ) {
    $this->fileRepository = $fileRepository;
    $this->fileStorage = $fileStorage;
    $this->fileSystem = $fileSystem;
    $this->fileUsage = $fileUsage;
    $this->storage = $storage;
    $this->tokenGenerator = $tokenGenerator;
  }

  public function create(FileInterface $file): FundingFileInterface {
    return $this->storage->create([
      'file_id' => $file->id(),
      'token' => $this->tokenGenerator->generateToken(),
    ]);
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function delete(FundingFileInterface $fundingFile): void {
    $file = $fundingFile->getFile();
    $this->deleteFileUsage($fundingFile);
    $this->storage->delete([$fundingFile]);
    if (NULL !== $file && 0 === count($this->fileUsage->listUsage($file))) {
      $this->fileStorage->delete([$file]);
    }
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onFilePreDelete(FileInterface $file): void {
    $fundingFile = $this->loadByFile($file);
    if (NULL !== $fundingFile) {
      $this->deleteFileUsage($fundingFile);
      $this->storage->delete([$fundingFile]);
    }
  }

  /**
   * @throws \Drupal\Core\File\Exception\FileException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function loadOrCreateByCiviUri(string $civiUri): FundingFileInterface {
    $fundingFile = $this->storage->loadOneByProperties(['civi_uri' => $civiUri]);
    if (NULL !== $fundingFile) {
      return $fundingFile;
    }

    $filename = rawurldecode(basename($civiUri));
    Assertion::notEmpty($filename);
    $fileUri = FundingFileInterface::DOWNLOAD_LOCATION . $filename;
    $downloadDir = dirname($fileUri);
    $this->fileSystem->prepareDirectory(
      $downloadDir,
      FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
    );

    $file = $this->fileRepository->writeData('', $fileUri);
    $fundingFile = $this->storage->create([
      'file_id' => $file->id(),
      'token' => $this->tokenGenerator->generateToken(),
      'civi_uri' => $civiUri,
    ]);

    $this->save($fundingFile);

    return $fundingFile;
  }

  public function loadByFile(FileInterface $file): ?FundingFileInterface {
    return $this->loadByFileId((string) $file->id());
  }

  public function loadByFileId(string $fileId): ?FundingFileInterface {
    return $this->storage->loadOneByProperties(['file_id' => $fileId]);
  }

  public function loadByFileUri(string $uri): ?FundingFileInterface {
    $file = $this->fileRepository->loadByUri($uri);

    return NULL === $file ? NULL : $this->loadByFile($file);
  }

  /**
   * @return array<FundingFileInterface>
   */
  public function loadByLastAccessBefore(int $lastAccess): array {
    $ids = $this->storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('last_access', $lastAccess, '<')
      ->execute();

    // @phpstan-ignore-next-line
    return $this->storage->loadMultiple($ids);
  }

  public function loadByToken(string $token): ?FundingFileInterface {
    return $this->storage->loadOneByProperties(['token' => $token]);
  }

  public function loadByTokenAndFilename(string $token, string $filename): ?FundingFileInterface {
    $fundingFile = $this->loadByToken($token);
    if (NULL === $fundingFile) {
      return NULL;
    }

    /** @var \Drupal\file\FileInterface $file */
    $file = $fundingFile->getFile();
    if ($file->getFilename() !== $filename) {
      return NULL;
    }

    return $fundingFile;
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save(FundingFileInterface $fundingFile): void {
    Assertion::notNull($fundingFile->getFile());
    $new = $fundingFile->isNew();
    if ($new) {
      $fundingFile->setLastAccess(time());
    }
    $this->storage->save($fundingFile);
    if ($new || !$this->hasFileUsage($fundingFile)) {
      $this->fileUsage->add(
        $fundingFile->getFile(),
        'civiremote_funding',
        $fundingFile->getEntityTypeId(),
        (string) $fundingFile->id(),
      );
    }
  }

  private function deleteFileUsage(FundingFileInterface $fundingFile): void {
    if (NULL !== $fundingFile->getFile()) {
      $this->fileUsage->delete(
        $fundingFile->getFile(),
        'civiremote_funding',
        $fundingFile->getEntityTypeId(),
        (string) $fundingFile->id(),
      );
    }
  }

  private function hasFileUsage(FundingFileInterface $fundingFile): bool {
    Assertion::notNull($fundingFile->getFile());
    $fileUsages = $this->fileUsage->listUsage($fundingFile->getFile());

    return ($fileUsages['civiremote_funding'][$fundingFile->getEntityTypeId()][$fundingFile->id()] ?? 0) > 0;
  }

}
