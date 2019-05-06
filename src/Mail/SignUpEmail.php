<?php
 
namespace App\Mail;
 
use Illuminate\Mail\Mailable;
 
class SignUpEmail extends Mailable
{
    protected $data;
 
    public function __construct($data)
    {
        $this->data = $data;
    }
 
    public function build()
    {
        return $this
            ->view('registro-exitoso.php')
            ->with($this->data)
            ->subject('Registro');
    }
}
