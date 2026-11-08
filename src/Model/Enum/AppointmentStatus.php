<?php
declare(strict_types=1);

namespace App\Model\Enum;

/**
 * Life cycle of an appointment request.
 *
 * pending -> approved | rejected | reschedule_proposed
 * reschedule_proposed -> approved (student accepts) | declined (student declines)
 * pending / approved -> cancelled (by the student)
 * approved -> completed (after the session)
 */
enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case RescheduleProposed = 'reschedule_proposed';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Approved => __('Confirmed'),
            self::Rejected => __('Not available'),
            self::RescheduleProposed => __('New time proposed'),
            self::Declined => __('Declined'),
            self::Cancelled => __('Cancelled'),
            self::Completed => __('Completed'),
        };
    }

    /** CSS class used for the status chip. */
    public function chipClass(): string
    {
        return match ($this) {
            self::Approved, self::Completed => 'chip chip-up',
            default => 'chip',
        };
    }

    /** True while the request still needs someone to act on it or is upcoming. */
    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Approved, self::RescheduleProposed], true);
    }
}