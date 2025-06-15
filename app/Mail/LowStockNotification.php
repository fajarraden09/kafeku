<?php
namespace App\Mail;
use App\Models\BahanBaku;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowStockNotification extends Mailable {
    use Queueable, SerializesModels;
    public $bahanBaku;
    public function __construct(BahanBaku $bahanBaku) { $this->bahanBaku = $bahanBaku; }
    public function build() {
        return $this->subject('Peringatan Stok Rendah: ' . $this->bahanBaku->nama_bahan)
                    ->view('emails.low_stock');
    }
}