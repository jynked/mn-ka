<?php

namespace App\Mail;

use App\Models\Quiz;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuizCompletionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $quiz;
    public $answers;
    public $contact;

    /**
     * Create a new message instance.
     */
    public function __construct(Quiz $quiz, array $answers, array $contact)
    {
        $this->quiz = $quiz;
        $this->answers = $answers; // Уже массив ответов
        $this->contact = $contact;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Результаты прохождения квиза: ' . $this->quiz->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.quiz-completion',
            with: [
                'quiz' => $this->quiz,
                'answers' => $this->answers,
                'contact' => $this->contact,
            ],
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
