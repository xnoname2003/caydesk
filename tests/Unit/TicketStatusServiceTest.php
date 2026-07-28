<?php

use App\Services\TicketStatusService;

test('allow valid state transactions', function () {
    expect(TicketStatusService::isValidTransition(
        TicketStatusService::STATUS_OPEN, 
        TicketStatusService::STATUS_ASSIGNED
    ))->toBeTrue();

    expect(TicketStatusService::isValidTransition(
        TicketStatusService::STATUS_RESOLVED, 
        TicketStatusService::STATUS_CLOSED
    ))->toBeTrue();
});

test('reject invalid transactions', function () {
    expect(TicketStatusService::isValidTransition(
        TicketStatusService::STATUS_OPEN, 
        TicketStatusService::STATUS_RESOLVED
    ))->toBeFalse();

    expect(TicketStatusService::isValidTransition(
        TicketStatusService::STATUS_CLOSED, 
        TicketStatusService::STATUS_IN_PROGRESS
    ))->toBeFalse();
});