<?php

namespace App\Services;

class TicketStatusService
{
    public const STATUS_OPEN = 'Open';
    public const STATUS_ASSIGNED = 'Assigned';
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_WAITING_FOR_CUSTOMER = 'Waiting for Customer';
    public const STATUS_RESOLVED = 'Resolved';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_REOPENED = 'Reopened';
    public const STATUS_ESCALATED = 'Escalated';

    protected static array $transitions = [
        self::STATUS_OPEN => [self::STATUS_ASSIGNED, self::STATUS_CLOSED],
        self::STATUS_ASSIGNED => [self::STATUS_IN_PROGRESS, self::STATUS_ESCALATED],
        self::STATUS_IN_PROGRESS => [self::STATUS_WAITING_FOR_CUSTOMER, self::STATUS_RESOLVED, self::STATUS_ESCALATED],
        self::STATUS_WAITING_FOR_CUSTOMER => [self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED],
        self::STATUS_RESOLVED => [self::STATUS_CLOSED, self::STATUS_REOPENED],
        self::STATUS_CLOSED => [self::STATUS_REOPENED],
        self::STATUS_REOPENED => [self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS],
        self::STATUS_ESCALATED => [self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED],
    ];

    public static function isValidTransition(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) return true;
        
        $allowed = self::$transitions[$currentStatus] ?? [];
        return in_array($newStatus, $allowed);
    }

    public static function getAllowedNextStatuses(string $currentStatus): array
    {
        $allowed = self::$transitions[$currentStatus] ?? [];
        return array_combine($allowed, $allowed);
    }

    public static function getAllStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_WAITING_FOR_CUSTOMER,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
            self::STATUS_REOPENED,
            self::STATUS_ESCALATED,
        ];
    }
}