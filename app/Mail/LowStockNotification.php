<?php

namespace App\Mail;

use App\Models\BahanBaku;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowStockNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $bahanBaku;
    public $subject; // Tambahkan ini
    public $messageContent; // Tambahkan ini (gunakan nama berbeda dari $message agar tidak konflik dengan properti Mailable)

    /**
     * Create a new message instance.
     *
     * @param BahanBaku $bahanBaku
     * @param string $subject
     * @param string $messageContent
     * @return void
     */
    public function __construct(BahanBaku $bahanBaku, string $subject, string $messageContent) // Perbarui konstruktor
    {
        $this->bahanBaku = $bahanBaku;
        $this->subject = $subject; // Simpan subjek
        $this->messageContent = $messageContent; // Simpan pesan
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Gunakan $this->subject yang sudah diteruskan untuk subjek email
        return $this->subject($this->subject)
                    // Gunakan view yang akan menampilkan $this->messageContent
                    ->view('emails.low_stock');
    }
}
