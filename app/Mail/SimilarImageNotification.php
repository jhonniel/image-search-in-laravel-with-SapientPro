<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SimilarImageNotification extends Mailable
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
        $config = config('similarity.email', []);
        $fromName = $config['from_name'] ?? config('mail.from.name', 'Image Search System');

        // Dynamic subject prefix based on notification type
        $notificationType = $this->data['notification_type'] ?? 'default';
        $status = $this->data['new_image_metadata']['status'] ?? 'item';

            switch ($notificationType) {
                case 'no_match':
                    $subjectPrefix = "📝 {$status} Uploaded - We'll Notify You";
                    break;
                case 'new_uploader':
                    $subjectPrefix = "🔍 Similar Item Found!";
                    break;
                default:
                    $subjectPrefix = "🔍 Similar Item Found!";
                    break;
            }

        return new Envelope(
            subject: $subjectPrefix . ' - ' . $fromName,
            from: new Address(
                $config['from_address'] ?? config('mail.from.address', 'noreply@example.com'),
                $fromName
            ),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.similar-image-notification',
            with: [
                'email' => $this->data['email'],
                'similarImages' => $this->data['similar_images'],
                'newImageMetadata' => $this->data['new_image_metadata'],
                'totalSimilar' => $this->data['total_similar'],
                'notification_type' => $this->data['notification_type']
            ]
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
