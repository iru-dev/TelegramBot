<?
class SendEMail
{
    static $transport = false;

    public $filename = 'index.html', // путь к html шаблону
        $tplFolder = '', // каталог с картинками
        $imgFolder = 'images/', $subject = '', $from = '', $fromName = null, $contentType =
        'text/html', $charset = 'utf-8';

    private $message, $data;

    public function __construct($options)
    {
        foreach ($options as $option => $value)
            $this->$option = $value;
        if (!self::$transport)
            self::$transport = Swift_SmtpTransport::newInstance();
    }

    public function Send($data, $email, $name = null)
    {
        $this->data = $data;
        $this->message = Swift_Message::newInstance();
        $mess = &$this->message;

        // подставляем данные в subject письма, если там есть соответствующие теги
        $subject = $this->SubstituteData($this->subject);
        $body = $this->GetBody();

        // email и имя получателя
        $mess->setTo($email, $name);
        // от кого
        $mess->setFrom($this->from, $this->fromName);
        // тема письма
        $mess->setSubject($subject);
        // сообщение
        $mess->setBody($body);
        $mess->setContentType($this->contentType);
        $mess->setCharset($this->charset);

        $mailer = Swift_Mailer::newInstance(self::$transport);
        return $mailer->send($this->message);
    }

    private function GetBody()
    {
        // считываем шаблон письма
        $body = file_get_contents($this->tplPath . $this->filename);
        // подставляем данные в шаблон
        $body = $this->SubstituteData($body);
        // аттачим все картинки, с подходящими imgPath и расширениями jpg, png, gif, заменяем атрибуты src в тегах img
        // 'self::AddImage' будет работать для php > 5.3, для 5.2 нужно заменить на array($this, 'AddImage')
        return preg_replace_callback('/' . preg_quote($this->imgPath, '/') . '((.+)\.(jpg|png|gif))/i',
            'self::AddImage', $body);
    }

    // прикрепляем картинку, меняем src
    private function AddImage($matches)
    {
        $path = $this->tplPath . "/" . $matches[0];
        return $this->message->embed(Swift_Image::fromPath($path));
    }

    // заменяем теги на соответствующие данные
    private function SubstituteData($str)
    {
        if (empty($this->data))
            return $str;
        foreach ($this->data as $k => $v)
            $str = str_replace($k, $v, $str);
        return $str;
    }
}
?>