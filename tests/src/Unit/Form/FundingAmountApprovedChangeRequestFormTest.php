<?php

declare(strict_types = 1);

namespace Drupal\Tests\civiremote_funding\Unit\Form;

use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\civiremote_funding\Form\FundingAmountApprovedChangeRequestForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class FundingAmountApprovedChangeRequestFormTest extends UnitTestCase {

  private MockObject | FundingApi $fundingApiMock;

  private FundingAmountApprovedChangeRequestForm $form;

  private MockObject | MessengerInterface $messengerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->fundingApiMock = $this->createMock(FundingApi::class);
    $this->form = new FundingAmountApprovedChangeRequestForm($this->fundingApiMock);

    $this->messengerMock = $this->createMock(MessengerInterface::class);

    $reflection = new \ReflectionClass(FundingAmountApprovedChangeRequestForm::class);
    $property = $reflection->getProperty('messenger');
    $property->setAccessible(TRUE);
    $property->setValue($this->form, $this->messengerMock);
  }

  public function testSubmitForm(): void {
    $formStateMock = $this->createMock(FormStateInterface::class);
    $formStateMock->expects(static::exactly(2))
      ->method('getValue')
      ->willReturnMap([
        ['amount', '10.5'],
        ['reason', 'Test reason'],
      ]);

    // buildForm sets fundingCaseId
    $this->form->buildForm([], $formStateMock, 123);

    $this->fundingApiMock->expects(static::once())
      ->method('createFundingAmountApprovedChangeRequest')
      ->with(123, 10.5, 'Test reason');

    // Messenger is called on success
    $this->messengerMock->expects(static::once())->method('addMessage');

    $form = [];
    $this->form->submitForm($form, $formStateMock);
  }

}
