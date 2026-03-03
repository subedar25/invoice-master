<?php

namespace App\Core\Email\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $view;
    public $data;

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @param string $view
     * @param array $data
     */
    public function __construct(string $subject, string $view, array $data = [])
    {
        $this->subject = $subject;
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view($this->view)
                    ->with($this->data);
    }
}