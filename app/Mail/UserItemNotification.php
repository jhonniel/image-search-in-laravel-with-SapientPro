<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserItemNotification extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'FindITFast - ';
        if ($this->data['notification_type'] === 'similar_item_found') {
            $subject .= 'Similar Item Found - We found a match for your ' . $this->data['item_type'] . ' item!';
        } elseif ($this->data['notification_type'] === 'item_matched') {
            $subject .= 'Item Match Found! - Someone has a matching ' . ($this->data['new_item_type'] ?? 'item') . ' item!';
        } elseif ($this->data['notification_type'] === 'new_item_uploaded') {
            $subject .= 'Item Uploaded Successfully - Your ' . $this->data['item_type'] . ' item has been added!';
        } elseif ($this->data['notification_type'] === 'item_claimed') {
            $subject .= 'Item Claimed - Someone wants to claim your ' . $this->data['item_type'] . ' item!';
        } else {
            $subject .= 'Item Notification';
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-item-notification',
            with: $this->data
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
