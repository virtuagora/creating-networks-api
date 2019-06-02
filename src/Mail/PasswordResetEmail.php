<?php
 
namespace App\Mail;
 
use Illuminate\Mail\Mailable;
 
class PasswordResetEmail extends Mailable
{
    protected $data;
    protected $locale;
 
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->locale = 'en';
    }

    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }
 
    public function build()
    {
        $template = $this->locale . '/welcome-email.php';
        $subjects = [
            'en' => 'Creating Networks - Password reset',
            'es' => 'Recupera tu clave en Creando Redes',
        ];
        return $this
            ->view($template)
            ->with($this->data)
            ->subject($subjects[$this->locale] ?? $subjects['en']);
    }
}
