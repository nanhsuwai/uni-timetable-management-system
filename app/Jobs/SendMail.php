<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $mail;
    public function __construct($to, $mail)
    {
        $this->to = $to;
        $this->mail = $mail;
    }

    public function handle()
    {
        Mail::to($this->to)->send($this->mail);
    }
}
