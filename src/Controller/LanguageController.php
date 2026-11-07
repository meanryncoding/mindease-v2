<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use DateTimeImmutable;

/**
 * Stores the visitor's language choice in a cookie and sends them back
 * to the page they came from. Works for guests and signed-in users.
 */
class LanguageController extends AppController
{
    /**
     * Languages the site supports.
     *
     * @var list<string>
     */
    private const SUPPORTED = ['en', 'ms'];

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['change']);
    }

    public function change(string $code): Response
    {
        if (in_array($code, self::SUPPORTED, true)) {
            $cookie = Cookie::create('mindease_lang', $code, [
                'expires' => new DateTimeImmutable('+1 year'),
                'path' => '/',
                'httponly' => true,
                'samesite' => Cookie::SAMESITE_LAX,
            ]);
            $this->response = $this->response->withCookie($cookie);
        }

        // referer() with $local = true only returns a path on this site, so it cannot redirect elsewhere.
        return $this->redirect($this->referer('/', true));
    }
}